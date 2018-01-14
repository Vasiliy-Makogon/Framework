<?php
/**
 * Категории с учетом региональности.
 * Вынесено из маппера категорий, дабы не захламлять основной маппер частным случаем.
 */
class Krugozor_Module_Category_Mapper_Regions extends Krugozor_Module_Category_Mapper_Category
{
    // Переопределяем свойства, что бы суперкласс не высчитывал имя модели и таблицы автоматически.
    protected $db_table_name = 'category';
    protected $model_class_name = 'Krugozor_Module_Category_Model_Category';

    /**
     * Получает дерево активных категорий уровня 0 с количеством активных элементов в каждом узле дерева.
     *
     * @param Krugozor_Module_User_Model_Territory модель города, региона или страны.
     * @return Krugozor_Cover_Array
     */
    public function findCategoriesFirstLevelWithCountElements(Krugozor_Module_User_Model_Territory $object)
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
     * @param Krugozor_Module_User_Model_Territory модель города, региона или страны.
     * @param Krugozor_Module_Category_Model_Category $category
     * @return int
     */
    public function findAdvertCountInRegionCategory(Krugozor_Module_User_Model_Territory $object, Krugozor_Module_Category_Model_Category $category)
    {
        $this->checkRegionObject($object);

        $sql = 'SELECT SUM(`count`)
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
     * @param Krugozor_Module_User_Model_Territory модель города, региона или страны.
     * @return int
     */
    public function findAdvertCountInRegion(Krugozor_Module_User_Model_Territory $object)
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
     * @param Krugozor_Module_User_Model_Territory $region модель города, региона или страны.
     * @param int $level
     * @return boolean|Krugozor_Cover_Array
     */
    public function findCategoriesByIdsWithCountElements(array $ids=array(), Krugozor_Module_User_Model_Territory $region, $level=0)
    {
        if (!$ids)
        {
            return new Krugozor_Cover_Array();
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

        $subtree = new Krugozor_Cover_Array();

        if (!$res)
        {
            return $subtree;
        }

        while ($row = $res->fetch_assoc())
        {
            $object = parent::createModelFromDatabaseResult($row);
            // $object->setIndent($level);
            $object->setTree($this->findCategoriesByIdsWithCountElements($object->getViewChildsAsArray(), $region, $level + 1));

            $subtree->append($object);

            if ($object->id)
            {
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
     * @param Krugozor_Module_User_Model_Territory $object
     * @return void
     * @throws RuntimeException
     */
    private function checkRegionObject(Krugozor_Module_User_Model_Territory $object)
    {
        switch ($object)
        {
            case $object instanceof Krugozor_Module_User_Model_Country:
                $this->table = 'advert-country_count';
                $this->field = 'id_country';
                break;

            case $object instanceof Krugozor_Module_User_Model_Region:
                $this->table = 'advert-region_count';
                $this->field = 'id_region';
                break;

            case $object instanceof Krugozor_Module_User_Model_City:
                $this->table = 'advert-city_count';
                $this->field = 'id_city';
                break;

            default:
                throw new RuntimeException(__METHOD__ . ': указан некорректный объект-регион ' . get_class($object));
        }
    }
}