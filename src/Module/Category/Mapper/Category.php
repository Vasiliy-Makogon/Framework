<?php

namespace Krugozor\Framework\Module\Category\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper\Tree;
use Krugozor\Framework\Model;
use Krugozor\Framework\Module\Category\Model\Category as CategoryModel;
use Krugozor\Framework\Statical\Numeric;

class Category extends Tree
{
    /**
     * Возвращает дочерние категории узла $id для подгрузки в динамический select-список.
     *
     * @param int ID категории, для которой необходмо получить потомков
     * @return CoverArray
     * @todo: использовать parent::loadLevel
     */
    public function getActiveChildCategories($id)
    {
        $sql = 'SELECT * FROM ?f
                WHERE `pid` = ?i AND `category_active` = 1
                ORDER BY `order` DESC';

        return parent::findModelListBySql($sql, $this->getTableName(), $id);
    }

    /**
     * Находит категорию по URL
     *
     * @param string $url
     * @return CategoryModel
     */
    public function findByUrl($url)
    {
        $params = array();
        $params['where'] = array(CategoryModel::getPropertyFieldName('url') . ' = "?s"' => array($url));

        return parent::findModelByParams($params);
    }

    /**
     * Сохраняет объект Категории и обновляет поле
     * сортировки order.
     *
     * @param CategoryModel
     */
    public function save(Model $object)
    {
        if (!$object->getId()) {
            parent::saveModel($object);
            $this->updateOrderField($object);

            $object->setUrlFromTreePath($this->loadPath($object->getId()));
            parent::saveModel($object);
        } else {
            $object->setUrlFromTreePath($this->loadPath($object->getId()));
            parent::saveModel($object);

            // получаем подчинённые узлы
            $tree = $this->loadSubtree($object->getId());
            // изменяем их URL-адреса
            $tree = $this->changeTreeUrls($tree, $object->getUrl());
            // сохраняем подчинённые
            $this->saveTree($tree);
        }
    }

    /**
     * Сохраняет дерево категорий.
     *
     * @param CoverArray дерево категорий
     */
    public function saveTree(CoverArray $tree)
    {
        if (!$tree->count()) {
            return false;
        }

        foreach ($tree as $category) {
            parent::saveModel($category);

            if ($category->getTree() && $category->getTree()->count()) {
                $this->saveTree($category->getTree());
            }
        }

        return true;
    }

    /**
     * Возвращает дерево активных категорий.
     *
     * @param int $indent максимальный уровень вложенности.
     * @return CoverArray
     */
    public function findActiveCategories($indent = null)
    {
        $params = array(
            'where' => array('category_active = ?i' => array(1)),
            'order' => array('order' => 'DESC')
        );

        if (Numeric::isDecimal($indent)) {
            $params['where']['AND category_indent <= ?i'] = array($indent);
        }

        return $this->loadTree($params);
    }

    /**
     * Возвращает дерево активных категорий первого уровня.
     *
     * @return CoverArray
     */
    public function findCategoriesFirstLevel()
    {
        $params['where'] = array();
        $params['where']['category_active = 1 and category_show_on_index = 1 and pid = 0'] = array();
        $params['order'] = array('order' => 'DESC');

        return $this->loadTree($params);
    }

    /**
     * Получает полное дерево на основании их идентификаторов.
     * Метод использует данные денормализации, хранящиеся в поле category_childs.
     * Данный метод делает на каждый узел SQL-запрос.
     *
     * @param array $ids
     * @param int $level
     * @return boolean|CoverArray
     */
    public function loadSubtreeByIds(array $ids = array(), $level = 0)
    {
        if (!$ids) {
            return new CoverArray();
        }

        $res = $this->getDb()->query('SELECT * FROM ?f WHERE `id` IN (?ai) ORDER BY `order` DESC', $this->getTableName(), $ids);

        $subtree = new CoverArray();

        if (!$res) {
            return $subtree;
        }

        while ($row = $res->fetch_assoc()) {
            $object = parent::createModelFromDatabaseResult($row);
            // $object->setIndent($level);
            $object->setTree($this->loadSubtreeByIds($object->getViewChildsAsArray(), $level + 1));

            $subtree->append($object);

            if ($object->id) {
                self::$collection[$this->getModuleName()][$this->getModelName()][$object->id] = $object;
            }
        }

        return $subtree;
    }

    /**
     * Изменяет URL адреса дерева, добавляя поочередно
     * к каждому следующему узлу префикс, состоящий из предыдущего URL.
     * В качестве начального URL передается строка $url.
     *
     * @param CoverArray $tree дерево категорий
     * @param $url префикс URL для всех URL адресов
     */
    private function changeTreeUrls(CoverArray $tree, $url)
    {
        if (!$tree->count()) {
            return new CoverArray();
        }

        foreach ($tree as $key => $category) {
            $tree->item($key)->setUrl($url . $tree->item($key)->getAlias() . '/');

            $tree->item($key)->setTree($this->changeTreeUrls($tree->item($key)->getTree(), $tree->item($key)->getUrl()));
        }

        return $tree;
    }

    /**
     * Методы для работы с "весом" строки в списке строк.
     *
     * Описание: После добавлении статьи берется значение поля id (autoincrement)
     * добавленной статьи и дублируется в поле order_id.
     * При нажатии кнопки "вверх" на текущей статье -
     * 1. беру максимальное предыдущее значение order_id не равное текущему (обменная статья)
     * 2. меняю order_id обменной статьи на временное (0)
     * 3. меняю order_id текущей статьи на order_id обменной статьи
     * 4. меняю order_id обменной статьи на order_id текущей статьи
     *
     * При необходимости вынести в трейты.
     */

    /**
     * Поднимает запись в иерархии на одну позицию выше.
     * Используя метод, нужно, быть уверенным в том,
     * что в таблице есть поле `order` предназначенное для сортировки.
     *
     * @param Model
     * @param array $category (описать этот аргумент)
     */
    public function motionUp(Model $object, array $category = array())
    {
        $sql_category = '';

        if ($category) {
            list($field, $value) = $category;
            $sql_category = ' AND `' . $field . '` = ' . $value;
        }

        $res = $this->getDb()->query('SELECT
                                    `id`,
                                    `order`
                                FROM
                                    `' . $this->getTableName() . '`
                                WHERE
                                    `order` >
                                    (
                                        SELECT
                                            `order`
                                        FROM
                                            `' . $this->getTableName() . '`
                                        WHERE
                                            `id` = ?i
                                        ' . $sql_category . '
                                    )
                                ' . $sql_category . '
                                ORDER BY `order` ASC
                                LIMIT 0, 1', $object->getId());

        list($down_id, $new_order) = $res->fetch_row();

        if ($down_id && $new_order) {
            $res = $this->getDb()->query('SELECT
                                        `order`
                                    FROM
                                        `' . $this->getTableName() . '`
                                    WHERE
                                        `id` = ?i ' . $sql_category, $object->getId());

            $down_order = $res->getOne();

            $this->getDb()->query('UPDATE
                                 `' . $this->getTableName() . '`
                             SET
                                 `order` = ?i
                             WHERE
                                 `id` = ?i' . $sql_category, $down_order, $down_id);

            $this->getDb()->query('UPDATE
                                 `' . $this->getTableName() . '`
                             SET
                                 `order` = ?i
                             WHERE
                                 `id` = ?i' . $sql_category, $new_order, $object->getId());
        }
    }

    /**
     * Опускает запись в иерархии на одну позицию ниже.
     * Используя метод, нужно, быть уверенным в том,
     * что в таблице есть поле `order` предназначенное для сортировки.
     *
     * @param Model
     * @param array $category (описать этот аргумент)
     */
    public function motionDown(Model $object, array $category = array())
    {
        $sql_category = '';

        if ($category) {
            list($field, $value) = $category;
            $sql_category = ' AND `' . $field . '` = ' . $value;
        }

        $res = $this->getDb()->query('SELECT
                                    `id`,
                                    `order`
                                FROM
                                    `' . $this->getTableName() . '`
                                WHERE
                                    `order` <
                                    (
                                        SELECT
                                            `order`
                                        FROM
                                            `' . $this->getTableName() . '`
                                        WHERE
                                            `id` = ?i
                                       )
                                ' . $sql_category . '
                                ORDER BY
                                    `order` DESC
                                LIMIT 0, 1', $object->getId());

        list($up_id, $new_order) = $res->fetch_row();

        if ($up_id && $new_order) {
            $res = $this->getDb()->query('SELECT
                                        `order`
                                    FROM
                                        `' . $this->getTableName() . '`
                                    WHERE
                                        `id` = ?i' . $sql_category, $object->getId());

            $up_order = $res->getOne();

            $this->getDb()->query('UPDATE
                                 `' . $this->getTableName() . '`
                             SET
                                 `order` = ?i
                             WHERE
                                 `id` = ?i' . $sql_category, $up_order, $up_id);

            $this->getDb()->query('UPDATE
                                 `' . $this->getTableName() . '`
                             SET
                                 `order` = ?i
                             WHERE
                                 `id` = ?i' . $sql_category, $new_order, $object->getId());
        }
    }

    /**
     * Обновляет поле `order` таблицы $this->getTableName() на ID только что вставленной записи.
     * Вызывается сразу после метода saveModel (вручную).
     * Применяется для таблиц, где используется сортировка ($this->motionUp() и $this->motionDown()).
     *
     * @param Model
     * @return Model
     */
    protected function updateOrderField(Model $object)
    {
        $fields_db = parent::getTableMetada();

        if (!empty($fields_db['order'])) {
            $sql = 'UPDATE ?f SET `order` = ?i WHERE `id` = ?i';

            $this->getDb()->query($sql,
                $this->getTableName(),
                $object->getId(),
                $object->getId()
            );
        }

        return $object;
    }

    /**
     * Получает всех активных прямых потомков уровня с идентификатором $id.
     *
     * (non-PHPdoc)
     */
    //     public function loadLevel($id, $params=array())
    //     {
    //         $params['where']['AND `category_active` = 1'] = array();

    //         return parent::loadLevel($id, $params);
    //     }

    /**
     * Возвращает родительскую категорию для категории $category.
     *
     * @param CategoryModel $category
     * @return CategoryModel
     */
    //     public function getParentCategory(CategoryModel $category)
    //     {
    //         $params = array(
    //             'where' => array(
    //                 CategoryModel::getPropertyFieldName('id') . ' = "?i"' => array($category->getPid())
    //             ),
    //         );

    //         $obj = parent::findModelByParams($params);

    //         return $obj;
    //     }
}