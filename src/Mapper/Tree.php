<?php
class Krugozor_Mapper_Tree extends Krugozor_Mapper_Common
{
    /**
     * Получает всех прямых потомков уровня с идентификатором $id.
     * Данный метод НЕ получает все дерево целиком, а только прямых потомков узла.
     *
     * @param int $id ID узла, прямых потомков которого необходимо получить
     * @param array $params параметры выборки
     * @return Krugozor_Cover_Array
     */
    public function loadLevel($id, $params=array())
    {
        if (empty($id) || !Krugozor_Static_Numeric::is_decimal($id)) {
            return false;
        }

        if (!isset($params['where'])) {
            $params['where'] = array();
        }

        Krugozor_Static_Array::array_unshift_assoc($params['where'], '`pid` = ?i', array($id));

        $params = self::makeSqlFromParams($params);

        $sql = 'SELECT ' . $params['what'] . '
                FROM `' . $this->getTableName() . '` ' .
                $params['where'] . '
                ORDER BY `order` DESC';

        array_unshift($params['args'], $sql);

        $res = call_user_func_array(array($this->getDb(), 'query'), $params['args']);

        $data = new Krugozor_Cover_Array();

        if (!$res) {
            return $data;
        }

        while ($row = $res->fetch_assoc()) {
            $object = parent::createModelFromDatabaseResult($row);

            if ($object->id) {
                self::$collection[$this->getModuleName()][$this->getModelName()][$object->getId()] = $object;
            }

            $data->append($object);
        }

        return $data;
    }

    /**
     * Получает полное дерево потомков узла $id.
     * Данный метод делает на каждый узел SQL-запрос.
     *
     * @param int $id ID узла, для которого выбираем потомков
     * @param array параметры выборки
     * @param int определения уровня
     * @return Krugozor_Cover_Array
     */
    public function loadSubtree($id, $params=array(), $level=0)
    {
        if (!Krugozor_Static_Numeric::is_decimal($id) || !$id) {
            return false;
        }

        if (!isset($params['where'])) {
            $params['where'] = array();
        }

        Krugozor_Static_Array::array_unshift_assoc($params['where'], '`pid` = ?i', array($id));

        $sql_params = self::makeSqlFromParams($params);

        $res = $this->getDb()->query('SELECT * FROM `' . $this->getTableName() . '` ' . $sql_params['where'] . ' ORDER BY `order` DESC', $id);

        if (!$res) {
            return new Krugozor_Cover_Array();
        }

        $subtree = new Krugozor_Cover_Array();

        while ($row = $res->fetch_assoc()) {
            $object = parent::createModelFromDatabaseResult($row);
            //$object->setIndent($level);
            $object->setTree($this->loadSubtree($object->getId(), $params, $level + 1));
            $subtree->append($object);

            if ($object->id) {
                self::$collection[$this->getModuleName()][$this->getModelName()][$object->id] = $object;
            }
        }

        return $subtree;
    }

    /**
     * Получает путь от начала дерева к указанной вершине.
     * Данный метод делает на каждый узел SQL-запрос.
     *
     * @param $id
     */
    public function loadPath($id)
    {
        if (empty($id) || !Krugozor_Static_Numeric::is_decimal($id, true)) {
            return false;
        }

        $tree = new Krugozor_Cover_Array();

        while ($id) {
            $object = parent::findModelById($id);

            if (!$object->getId()) {
                return false;
            }

            $object->setTree($tree);

            $tree = new Krugozor_Cover_Array();
            $tree->append($object);

            $id = $object->getPid();
        }

        return $tree;
    }

    // Методы ниже оперируют промежуточным массивом - построение дерева и его узлов строится НЕ по принципу
    // выборки каждого следующего узла с помощью SQL-запроса, а с помощью одного SQL-запроса, который исполняет
    // метод self::findMediumTypeArray(). Метод возвращает многомерный массив, который принимает на вход метод
    // self::medium2objectTree(), который и создает дерево.

    // Данные методы не могу применяться для случаев, когда необходимо строить дерево на основании данных из
    // искомых узлов, т.к. SQL-запрос выполняется единожды и подразумевается, что этот единственный SQL должен
    // сразу найти все записи, которые необходимы для построения дерева.

    /**
     * Загружает все дерево в соответствии с массивом $params.
     *
     * @param array массив параметро выборки
     * @return Cover_Array
     */
    public function loadTree($params=array())
    {
        $marray = $this->findMediumTypeArray($params);
        // Получаем ID самого верхнего родителя, что бы начать с него построение дерева.
        $key = $marray ? min(array_keys($marray)) : 0;
        return $this->medium2objectTree($marray, $key);
    }

    /**
     * Возвращает многомерный массив вида:
     *
     * [0] => Array
     *  (
     *      [0] => Array
     *          (
     *              [id] => 121
     *              [pid] => 0
     *              [category_name] => Недвижимость
     *              [...] => ...
     *          )
     * [50] => Array
     *  (
     *      [0] => Array
     *          (
     *              [id] => 82
     *              [pid] => 50
     *              [category_name] => Женская одежда
     *              [...] => ...
     *
     * где каждый элемент массива является элементом массива
     * с ключом равным его parent id.
     *
     * @param array $params
     * @return array
     */
    protected function findMediumTypeArray(array $params=array())
    {
        $params = self::makeSqlFromParams($params);

        $sql = 'SELECT ' . $params['what'] .
               ' FROM `' . $this->getTableName() . '`' .
               $params['join'] .
               $params['where'] .
               $params['order'];

        array_unshift($params['args'], $sql);

        $res = call_user_func_array(array($this->getDb(), 'query'), $params['args']);

        $data = array();

        while ($temp = $res->fetch_assoc()) {
            if (!isset($data[$temp['pid']])) {
                $data[$temp['pid']] = array();
            }

            $data[$temp['pid']][] = $temp;
        }

        return $data;
    }

    /**
     * Создает дерево объектов из многомерного массива,
     * возвращённого методом $this->findMediumTypeArray()
     *
     * @param array $data массив
     * @param int $k идентификатор элемента
     * @param int $indent отступ для форматирования
     * @return Krugozor_Cover_Array
     */
    protected function medium2objectTree($data, $k=0, $indent=0)
    {
        if (empty($data[$k])) {
            return new Krugozor_Cover_Array();
        }

        $indent++;

        $tree = new Krugozor_Cover_Array();

        foreach ($data[$k] as $category_data) {
            $object = parent::createModelFromDatabaseResult($category_data);
            $object->setTree($this->medium2objectTree($data, $category_data['id'], $indent));
            //$object->setIndent($indent);
            $tree->append($object);
        }

        return $tree;
    }
}