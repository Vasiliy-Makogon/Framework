<?php

namespace Krugozor\Framework\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper;
use Krugozor\Framework\Model;
use Krugozor\Framework\Type\Datetime;

class CommonMapper extends Mapper
{
    /**
     * Метод для JOIN-выборок (возвращение многомерного массива объектов моделей).
     * Принимает SQL-запрос и в случае необходимости, Н-ное кол-во аргументов-заполнителей.
     *
     * @return bool|false|CoverArray
     */
    public final function join()
    {
        if (!func_num_args() or func_num_args() && (!func_get_arg(0) || !is_string(func_get_arg(0)))) {
            return false;
        }

        $res = call_user_func_array(array($this->getDb(), 'query'), func_get_args());

        return parent::result2objects($res);
    }

    /**
     * Находит объект на основании параметра $objId.
     * $objId может быть либо объектом со свойством id, либо числовым значением.
     * Если объект не найден в СУБД, возвращает новый пустой объект этой модели.
     *
     * @param Model|int
     * @return Model
     */
    public function findModelById($objId): Model
    {
        $id = is_object($objId) && $objId instanceof Model ? $objId->getId() : $objId;

        if (!$id) {
            return $this->createModel();
        }

        if (!isset(self::$collection[$this->getModuleName()][$this->getModelName()][$id])) {
            return $this->findModelByParams(
                array('where' => array('`id` = ?i' => array($id)))
            );
        }

        return self::$collection[$this->getModuleName()][$this->getModelName()][$id];
    }

    /**
     * Находит объекты по массиву их идентификаторов.
     *
     * @param array $ids
     * @return CoverArray
     */
    public function findModelListByIds(array $ids=array()): CoverArray
    {
        $params['where'] = array('id IN (?ai)' => array($ids));

        return $this->findModelListByParams($params);
    }

    /**
     * Возвращает доменный объект на основании параметров $params.
     *
     * @param array параметры выборки
     * @return Model
     */
    public final function findModelByParams(array $params=array()): Model
    {
        $res = parent::createQuerySelect($params);

        $object = $this->createModelFromDatabaseResult( is_object($res) && $res->getNumRows() ? $res->fetch_assoc() : array() );

        if ($object->getId()) {
            self::$collection[$this->getModuleName()][$this->getModelName()][$object->getId()] = $object;
        }

        return $object;
    }

    /**
     * Возвращает объект CoverArray, содержащий список объектов
     * выбранных согласно массиву параметров $params.
     *
     * @param array параметры выборки
     * @return CoverArray
     */
    public final function findModelListByParams(array $params = array()): CoverArray
    {
        $data = new CoverArray();

        $res = parent::createQuerySelect($params);

        if (is_object($res) && $res->getNumRows()) {
            while ($row = $res->fetch_assoc()) {
                $object = $this->createModelFromDatabaseResult($row);

                self::$collection[$this->getModuleName()][$this->getModelName()][$object->id] = $object;

                $data->append($object);
            }
        }

        return $data;
    }

    /**
     * Получает значение FOUND_ROWS()
     *
     * @return string
     */
    public function getFoundRows(): string
    {
        return $this->getDb()->query('SELECT FOUND_ROWS()')->getOne();
    }

    /**
     * Удаляет запись(и) из таблицы согласно массиву параметров $params.
     *
     * @param array
     * @return int количество удаленных рядов
     */
    public function deleteByParams(array $params=array()): int
    {
        parent::createQueryDelete($params);

        return $this->getDb()->getAffectedRows();
    }

    /**
     * Удаляет одну запись из таблицы согласно $objId.
     * $objId может быть либо объектом со свойством ID, либо числовым значением.
     *
     * @param Model|int
     * @return int количество удаленных рядов
     */
    public function deleteById($objId)
    {
        $params = array('where' => array('id = ?i' => array(is_object($objId) ? $objId->id : $objId)),
                        'limit' => array('start' => 1));

        parent::createQueryDelete($params);

        return $this->getDb()->getAffectedRows();
    }

    /**
     * Сохраняет объект в БД.
     *
     * @param Model $object
     * @return Model
     * @throws \Krugozor\Framework\LogicException
     * @todo Добавить сюда остальные типы полей для даты
     */
    public function saveModel(Model $object): Model
    {
        $args = $track_args = array();

        $sql = $object->getId() ? 'UPDATE ?f SET ' : 'INSERT INTO ?f SET ';

        $args[] = $this->getTableName();

        foreach ($object->getData() as $key => $value) {
            if (!$object::getPropertyDbElement($key)) {
                continue;
            }

            $property_type = $object::getPropertyType($key);

            // Если ID объекта существует, значит - это обновление таблицы.
            // Делаем проверку, были ли поля в объекте изменены.
            if ($object->getId() && $object->getTrack()->getData()) {
                if ($object->getTrack()->compare($key, $value)) {
                    continue;
                }
            }

            if (is_object($value)) {
                $track_args[$key] = $value;

                // Объект Module_Type_Datetime обрабатываем "особо" в виду его сложности
                // и фактической пригодности для нескольких видов полей таблицы.
                if ($value instanceof Datetime) {
                    $fields_info = parent::getTableMetada();

                    $db_field_name = ($object->getDbFieldPrefix() ? $object->getDbFieldPrefix() . '_' : '') . $key;

                    if (!empty($fields_info[$db_field_name])) {
                        switch ($fields_info[$db_field_name]->type) {
                            // YEAR
                            case '13':
                                $sql .= '?f = "?s", ';
                                $args[] = $object::getPropertyFieldName($key);
                                $args[] = $value->format('Y');
                                break;

                            // DATETIME
                            case '12':
                                $sql .= '?f = "?s", ';
                                $args[] = $object::getPropertyFieldName($key);
                                $args[] = $value->format('Y-m-d H:i:s');
                                break;

                            // TIME
                            case '11':
                                // todo
                                break;

                            // DATE
                            case '10':
                                $sql .= '?f = "?s", ';
                                $args[] = $object::getPropertyFieldName($key);
                                $args[] = $value->format('Y-m-d');
                                break;

                            // TIMESTAMP
                            case '7':
                                $sql .= '?f = ?i, ';
                                $args[] = $object::getPropertyFieldName($key);
                                $args[] = $value->getTimestamp();
                                break;
                        }
                    }
                } else if ($value instanceof $property_type && method_exists($value, 'getValue')) {
                    // Пустые строки в базу не пишем, вместо них пишем NULL
                    if ($value->getValue() === null || $value->getValue() === '') {
                        $sql .= '?f = NULL, ';
                        $args[] = $object::getPropertyFieldName($key);
                        $track_args[$key] = null;
                    } else {
                        $sql .= '?f = "?s", ';
                        $args[] = $object::getPropertyFieldName($key);
                        $args[] = $value->getValue();
                        $track_args[$key] = $value;
                    }
                }
            } else {
                // Пустые строки в базу не пишем, вместо них пишем NULL
                if ($value === null || $value === '') {
                    $sql .= '?f = NULL, ';
                    $args[] = $object::getPropertyFieldName($key);
                    $track_args[$key] = null;
                } else {
                    $sql .= '?f = "?s", ';
                    $args[] = $object::getPropertyFieldName($key);
                    $args[] = $value;
                    $track_args[$key] = $value;
                }
            }
        }

        // Свойства объекта не были изменены - попытка сохранить объект модели с такими же данными.
        if (count($args) === 1) {
            return $object;
        }

        $sql = rtrim($sql, ', ');

        if ($object->getId()) {
            $args[] = $object->getId();
            $sql = $sql . ' WHERE id = ?i';

            $this->getDb()->queryArguments($sql, $args);
        } else {
            $this->getDb()->queryArguments($sql, $args);
            $object->setId($this->getDb()->getLastInsertId());
        }

        // После сохранения обновляем Track, т.к. изменились актуальные св-ва объекта.
        // Фактически, делаем вид что достали объект из базы (этот код - экономия на SQL запросе).
        $object->getTrack()->setData($track_args);

        return $object;
    }
}