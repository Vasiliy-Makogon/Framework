<?php

namespace Krugozor\Framework\Module\Category\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Module\User\Model\City;
use Krugozor\Framework\Module\User\Model\Country;
use Krugozor\Framework\Module\User\Model\Region;
use Krugozor\Framework\Module\User\Model\Territory;
use Krugozor\Framework\Module\Category\Model\Category;
use Krugozor\Framework\Module\Category\Mapper\Category as CategoryMapper;

/**
 * Категории с учетом региональности.
 * Вынесено из маппера категорий, дабы не захламлять основной маппер частным случаем.
 */
class Regions extends CategoryMapper
{
    // Переопределяем свойства, что бы суперкласс не высчитывал имя модели и таблицы автоматически.
    /**
     * @var string
     */
    protected $db_table_name = 'category';

    /**
     * @var string
     */
    protected $model_class_name = '\Krugozor\Framework\Module\Category\Model\Category';

    /**
     * Получает дерево активных категорий уровня 0 с количеством активных элементов в каждом узле дерева.
     *
     * @param Territory $object
     * @return CoverArray
     */
    public function findCategoriesFirstLevelWithCountElements(Territory $object): CoverArray
    {
        $this->checkRegionObject($object);

        $params['where'] = array('category_active = 1 and category_show_on_index = 1 and pid = 0' => array());
        $params['order'] = array('order' => 'DESC');
        $params['what'] = '
            *, COALESCE(
                (SELECT SUM(`count`)
                FROM `' . $this->table . '` AS t
                WHERE FIND_IN_SET(t.id_category, CONCAT_WS(",", id, category_all_childs))
                AND t.' . $this->field . ' = ' . $object->getId() . '),0) AS `advert_count`'; // делаем вид, что `advert_count` достали из таблицы категорий.

        return $this->loadTree($params);
    }

    /**
     * Возвращает количество объявлений в регионе $object категории $category.
     *
     * @param Territory $object модель города, региона или страны.
     * @param Category $category
     * @return mixed
     */
    public function findAdvertCountInRegionCategory(Territory $object, Category $category)
    {
        $this->checkRegionObject($object);

        $sql = '
            SELECT SUM(`count`)
            FROM `' . $this->table . '` AS t
            WHERE t.' . $this->field . ' = ' . $object->getId() . '
            AND t.id_category IN (?ai)';

        $ids = $category->getAllChildsAsArray();
        $ids[] = $category->getId();

        return $this->getDb()->query($sql, $ids)->getOne();
    }

    /**
     * Возвращает количество объявлений в регионе $object.
     *
     * @param Territory $object модель города, региона или страны.
     * @return mixed
     */
    public function findAdvertCountInRegion(Territory $object)
    {
        $this->checkRegionObject($object);

        $sql = 'SELECT SUM(`count`)
                FROM `' . $this->table . '` AS t
                WHERE t.' . $this->field . ' = ' . $object->getId();

        return $this->getDb()->query($sql)->getOne();
    }

    /**
     * Получает полное дерево на основании их идентификаторов, с количеством активных элементов в каждом узле дерева.
     * Метод использует данные денормализации, хранящиеся в поле category_childs.
     * Данный метод делает на каждый узел SQL-запрос.
     *
     * @param array $ids идентификаторы узлов
     * @param Territory $object модель города, региона или страны.
     * @param int $level
     * @return CoverArray
     */
    public function findCategoriesByIdsWithCountElements(array $ids = array(), Territory $region, $level = 0): CoverArray
    {
        if (!$ids) {
            return new CoverArray();
        }

        $this->checkRegionObject($region);

        $sql = '
            SELECT *,
            COALESCE(
                (SELECT SUM(`count`)
                FROM `' . $this->table . '` AS t
                WHERE FIND_IN_SET(t.id_category, CONCAT_WS( ",", id, category_all_childs))
                AND t.' . $this->field . ' = ?i), 0
            ) AS `advert_count`
            FROM ' . $this->getTableName() . '
            WHERE category_active = 1
            AND category_show_on_index = 1
            AND `id` IN (?ai)
            ORDER BY `order` DESC
        ';

        $res = $this->getDb()->query($sql, $region->getId(), $ids);

        $subtree = new CoverArray();

        if (!$res) {
            return $subtree;
        }

        while ($row = $res->fetch_assoc()) {
            $object = parent::createModelFromDatabaseResult($row);
            // $object->setIndent($level);
            $object->setTree($this->findCategoriesByIdsWithCountElements($object->getViewChildsAsArray(), $region, $level + 1));

            $subtree->append($object);

            if ($object->id) {
                self::$collection[$this->getModuleName()][$this->getModelName()][$object->id] = $object;
            }
        }

        return $subtree;
    }

    /**
     * Проверяет $object на принадлежность объектам-регионами и в случае успеха,
     * инициализирует в this две переменные - имя таблицы с количеством объявлений в регионе и поля,
     * на которое необходимо для выборки.
     *
     * @param Territory $object модель города, региона или страны.
     * @throws \RuntimeException
     */
    private function checkRegionObject(Territory $object)
    {
        switch ($object) {
            case $object instanceof Country:
                $this->table = 'advert-country_count';
                $this->field = 'id_country';
                break;

            case $object instanceof Region:
                $this->table = 'advert-region_count';
                $this->field = 'id_region';
                break;

            case $object instanceof City:
                $this->table = 'advert-city_count';
                $this->field = 'id_city';
                break;

            default:
                throw new \RuntimeException(
                    __METHOD__ . ': указан некорректный объект-регион ' . get_class($object)
                );
        }
    }
}