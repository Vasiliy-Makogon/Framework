<?php
abstract class Krugozor_Mapper
{
    /**
     * Разделитель имени модуля и модели в имени таблицы БД.
     * Пример:
     * user-phone - модуль user, модель phone.
     * user-home_phone - модуль user, модель home_phone.
     *
     * @var string;
     */
    const TABLE_SEPARATOR = '-';

    /**
     * Имя таблицы модели.
     * Заполняется по требованию в методе $this->getTableName() на основании имени конкретного объекта маппера.
     *
     * Примеры (путь к мапперу => имя таблицы в СУБД):
     * Krugozor/Module/User/Mapper/User             => user
     * Krugozor/Module/User/Mapper/City             => user-city
     * Krugozor/Module/SuperUser/Mapper/DefaultCity => super_user-default_city
     *
     * В случае необходимости, когда имя таблицы может отличаться от стандартизованного формата `[имя_модуля]-[имя_модели]`,
     * данное свойство можно объявить явно в конкретном классе, тогда имя таблицы не будет высчитываться из имени класса.
     *
     * @var string
     */
    protected $db_table_name;

    /**
     * Имя класса модели.
     * Заполняется по требованию в методе $this->getModelClassName()
     *
     * @var string
     */
    protected $model_class_name;

    /**
     * Имя модели, связанной с экземпляром текущего объекта меппера.
     * Заполняется по требованию в методе $this->getModelName()
     *
     * @var string
     */
    protected $model_name;

    /**
     * Имя модуля, связанного с экземпляром текущего объекта меппера.
     * Заполняется по требованию в методе $this->getModuleName()
     *
     * @var string
     */
    protected $module_name;

    /**
     * Кэш-коллекция объектов уже полученных из БД.
     * Коллекция представляет собой многомерный массив вида
     * $collection[Module_Name][Model_Name][object->id] = object
     *
     * @todo: разобраться, насколько эта коллекция нужна и по возможности
     *        от неё отказаться, либо следить за целостностью данных в коллекции и в базе.
     * @var array
     */
    protected static $collection = array();

    /**
     * Мэнеджер мэпперов.
     *
     * @var Krugozor_Mapper_Manager
     */
    private $mapperManager;

    /**
     * Кэш-коллекция объектов, представляющих поля результирующего набора
     * полученние с помощью mysqli_fetch_fields.
     *
     * @var array
     */
    private static $list_fields = array();

    /**
     * @param Krugozor_Mapper_Manager $manager
     */
    public function __construct(Krugozor_Mapper_Manager $mapperManager)
    {
        $this->mapperManager = $mapperManager;
    }

    /**
     * Возвращает объект хранилища инстанцированных мепперов Krugozor_Mapper_Manager.
     *
     * @param void
     * @return Krugozor_Mapper_Manager
     */
    protected final function getMapperManager()
    {
        return $this->mapperManager;
    }

    /**
     * Возвращает объект базы данных.
     * Данный метод - сокращеная запись получения объекта СУБД, что бы не писать
     * каждый раз $this->getMapperManager()->getDb().
     *
     * @param void
     * @return \Krugozor\Database\Mysql\Mysql
     */
    protected final function getDb()
    {
        return $this->getMapperManager()->getDb();
    }

    /**
     * Возвращает имя класса модели.
     * Например: Krugozor_Module_User_Model_User
     *
     * @param void
     * @return string
     */
    public function getModelClassName()
    {
        if ($this->model_class_name === null) {
            $this->injectModelInfo();

            $this->model_class_name = $this->getModelClassNameByParts($this->module_name, $this->model_name);
        }

        return $this->model_class_name;
    }

    /**
     * Возвращает имя модели.
     * Например: User
     *
     * @param void
     * @return string
     */
    public function getModelName()
    {
        if (null === $this->model_name) {
            $this->injectModelInfo();
        }

        return $this->model_name;
    }

    /**
     * Возвращает имя модуля моделя.
     * Например: User
     *
     * @param void
     * @return string
     */
    public function getModuleName()
    {
        if (null === $this->module_name) {
            $this->injectModelInfo();
        }

        return $this->module_name;
    }

    /**
     * Возвращает имя таблицы БД, ассоциируемой с данным маппером.
     * См. описание свойства $this->db_table_name.
     *
     * @param void
     * @return string
     */
    public function getTableName()
    {
        if (null === $this->db_table_name) {
            $this->injectModelInfo();

            $module = Krugozor_Static_String::camelCaseToProperty($this->module_name);
            $controller = Krugozor_Static_String::camelCaseToProperty($this->model_name);

            // Если имя модуля совпадает с именем контроллера, то таблица будет без разделителя.
            $this->db_table_name = $module === $controller ? $module : $module . self::TABLE_SEPARATOR . $controller;
        }

        return $this->db_table_name;
    }

    /**
     * Удаляет элемент коллекци self::$collection.
     *
     * @param string $module_name имя модуля
     * @param string $model_name имя модели
     * @param int $id ID объекта
     * @return void
     */
    public static function unsetCollectionElement($module_name, $model_name, $id)
    {
        unset(self::$collection[$module_name][$model_name][$id]);
    }

    /*********************************************************************************
    *    П О Р А Ж Д А Ю Щ И Е    О Б Ъ Е К Т Ы    М Е Т О Д Ы
    **********************************************************************************/

    /**
     * Создает пустой объект модели на основе карты
     * опций аттрибутов модели Krugozor_Model::model_attributes.
     *
     * Значениями аттрибутов объекта становятся значения по умолчанию,
     * определенные в карте опций модели под индексами 'default_value'.
     * Если значения по умолчанию не заданы в карте модели, то свойства
     * задаются со значением null.
     *
     * @param void
     * @return Krugozor_Model
     */
    public function createModel()
    {
        $model_class_name = $this->getModelClassName();
        $object = new $model_class_name();
        $object->setMapperManager($this->getMapperManager());

        foreach ($object->getModelsPropertiesSettings() as $key => $params) {
            if ($method_name = $object->getMethodNameByKeyWithPrefix($key, 'set')) {
                $object->$method_name(isset($params['default_value']) ? $params['default_value'] : null);
            }
        }

        return $object;
    }

    /**
     * Создает доменный объект из одномерного массива $data, который
     * представляют собой результат выборки из СУБД.
     *
     * @todo: что будет, если в результате будет не полное количество полей?
     *        возможно, необходимо применить методику заполнения полей значениями по умолчанию,
     *        как в методе createModel()
     * @param array
     * @return Krugozor_Model
     * @final
     */
    protected final function createModelFromDatabaseResult(array $data)
    {
        // SQL-запрос вернул результат, запись найдена.
        if ($data) {
            $model_class_name = $this->getModelClassName();
            $object = new $model_class_name();
            $object->setMapperManager($this->getMapperManager());
            $object->setData($data);
            return $object;
        }

        // Запись не найдена в базе - возвращаем пустую модель.
        return $this->createModel();
    }

    /**
     * Принимает результат выполнения SQL-запроса в виде ресурса \Krugozor\Database\Mysql\Statement
     * и возвращает массив объектов моделей, созданых на основе результата выборки.
     * Основной метод для получения списка объектов на основе JOIN-запроса.
     *
     * @param \Krugozor\Database\Mysql\Statement $statement
     * @return Krugozor_Cover_Array|false
     */
    protected final function result2objects(\Krugozor\Database\Mysql\Statement $statement)
    {
        if (!is_object($statement) || !$statement instanceof \Krugozor\Database\Mysql\Statement) {
            return false;
        }

        $result = new Krugozor_Cover_Array();

        $fielsd = $statement->getResult()->fetch_fields();

        while ($row = $statement->fetch_row()) {
            $temp = array();

            for ($i=0; $i < count($fielsd); $i++) {
                // Поле из результата выборки, не являющееся полем какой-либо таблицы из запроса.
                // Например, полученное через " COUNT(*) AS `blablabla` ".
                // Создаем иммитацию реально существующей таблицы, что бы была возможность получить это значение из
                // объекта.
                if (!$fielsd[$i]->orgtable) {
                    // Если при выборке алиас представлен в виде `имя_таблицы__имя_поля` (два подчеркивания)
                    // то делаем вид, что `имя_поля` это поле таблицы `имя_таблицы` (соответственно, `имя_поля` - это
                    // свойство модели `имя_таблицы`).
                    // ВАЖНО! Свойство должно быть объявлено в карте аттрибутов моделей, иначе присваивание
                    // свойства `имя_поля` модели `имя_таблицы` будет проигнорировано!
                    if (preg_match('~^(.+)__(.+)$~', $fielsd[$i]->name, $matches)) {
                        $temp[$matches[1]][$matches[2]] = $row[$i];
                    }
                    // Иначе кладем его в fake-объект.
                    else {
                        $temp['common' . self::TABLE_SEPARATOR . 'fake'][$fielsd[$i]->name] = $row[$i];
                    }
                } else {
                    $temp[$fielsd[$i]->orgtable][$fielsd[$i]->name] = $row[$i];
                }
            }

            $result_element = array();

            foreach ($temp as $table_name => $props) {
                $temps = explode(self::TABLE_SEPARATOR, $table_name, 2);

                if (count($temps) == 2) {
                    $module_name = $temps[0];
                    $model_name = $temps[1];
                } else {
                    $module_name = $model_name = $temps[0];
                }

                $current_model_class_name = $this->getModelClassNameByParts($module_name, $model_name);
                $current_model = new $current_model_class_name();
                $current_model->setMapperManager($this->getMapperManager());
                $current_model->setData($props);
                $result_element[$model_name] = $current_model;

                if (isset($current_model->id)) {
                    self::$collection[$module_name][$model_name][$current_model->getId()] = $current_model;
                }
            }

            $result->append($result_element);
        }

        return $result;
    }

    /*********************************************************************************
    *       М Е Т О Д Ы    В Ы Б О Р К И
    **********************************************************************************/

    /**
     * Исполняет простой SELECT-запрос к текущей таблице на основании параметров
     * $params и возвращает объект результата \Krugozor\Database\Mysql\Statement.
     *
     * @param array
     * @return \Krugozor\Database\Mysql\Statement
     * @final
     */
    protected final function createQuerySelect($params): \Krugozor\Database\Mysql\Statement
    {
        $params = self::makeSqlFromParams($params);

        $sql = 'SELECT' . $params['what'] . 'FROM ?f' . $params['join'] . $params['where'] . $params['order'] . $params['limit'];

        array_unshift($params['args'], $this->getTableName());
        array_unshift($params['args'], $sql);

        $result = call_user_func_array(array($this->getDb(), 'query'), $params['args']);

        return $result;
    }

    /**
     * Возвращает доменный объект на основании SQL-запроса.
     *
     * Не изменять область видимости protected - данный метод должен использоваться в конкретных публичных методах,
     * инкапсулирующих SQL-запрос.
     *
     * @param array параметры выборки
     * @return Krugozor_Model
     * @final
     */
    protected final function findModelBySql()
    {
        if (!func_num_args() or func_num_args() && (!func_get_arg(0) || !is_string(func_get_arg(0)))) {
            return false;
        }

        $res = call_user_func_array(array($this->getDb(), 'query'), func_get_args());

        $object = $this->createModelFromDatabaseResult( is_object($res) && $res->getNumRows() ? $res->fetch_assoc() : array() );

        if ($object->getId()) {
            self::$collection[$this->getModuleName()][$this->getModelName()][$object->getId()] = $object;
        }

        return $object;
    }

    /**
     * Возвращает объект Krugozor_Cover_Array, содержащий список объектов
     * выбранных согласно SQL запросу.
     * Первым аргументом должен быть SQL-запрос,
     * последующими необязательными аргументами - значения для подстановки в заполнители SQL-запроса.
     *
     * Не изменять область видимости protected - данный метод должен использоваться в конкретных публичных методах,
     * инкапсулирующих SQL-запрос.
     *
     * @param string SQL-запрос
     * @param [mixed $argument [, mixed $... ]]
     * @return Krugozor_Cover_Array
     */
    protected final function findModelListBySql()
    {
        if (!func_num_args() or func_num_args() && (!func_get_arg(0) || !is_string(func_get_arg(0)))) {
            return false;
        }

        $data = new Krugozor_Cover_Array();

        $res = call_user_func_array(array($this->getDb(), 'query'), func_get_args());

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
     * Исполняет DELETE-запрос и возвращает объект результата \Krugozor\Database\Mysql\Statement.
     *
     * @param array $params параметры выборки
     * @return \Krugozor\Database\Mysql\Statement
     * @final
     */
    protected final function createQueryDelete(array $params)
    {
        $params = self::makeSqlFromParams($params);

        $sql = 'DELETE FROM ?f ' . $params['where'] . $params['limit'];

        array_unshift($params['args'], $this->getTableName());
        array_unshift($params['args'], $sql);

        return call_user_func_array(array($this->getDb(), 'query'), $params['args']);
    }

    /**
     * Метод формирования SQL запросов из массива параметров params.
     * Массив параметров представляет собой ассоциативный массив, где ключи являются
     * условиями и элементами SQL запроса, а значения - данные для подстановки в SQL.
     *
     * 1. 'where'.
     * 'where' может быть массивом вида
     * $params['where'] = array
     * (
     *     'id = ?' => array(23),
     *     'id = ? AND foo = "?" AND foo2 LIKE "?!" ' => array(23, 'hellow', 'world')
     * );
     * или строкой вида
     * $params['where'] = 'id = 5';
     * 'where' может быть не определён, тогда where-условие не используется.
     *
     * 2. 'what'
     * 'what' может являться строкой вида
     * $params['what'] = 'name, value';
     * 'what' может быть не определён, тогда what-условие по умолчанию становится как *.
     *
     * 3. 'limit'
     * 'limit' может является массивом вида
     * $params['limit'] = array('start' => int [, 'stop' => int]);
     * 'limit' может быть не определён, тогда limit-условие не используется.
     *
     * 4. 'order'
     * 'order' может является массивом вида
     * $params['order'] = array
     * (
     *     'col' => 'ASC|DESC' [, 'col2' => 'ASC|DESC']
     * )
     * где 'col' - столбец, по которому производится сортировка
     *     'ASC|DESC' - один из двух методов сортировки
     * 'order' может быть не определён, тогда order-условие не используется.
     *
     * 5. 'group'
     * 'group' может является массивом вида
     * $params['group'] = array
     * (
     *     'col' => 'ASC|DESC' [, 'col2' => 'ASC|DESC']
     * )
     * где 'col' - столбец, по которому производится группировка
     *     'ASC|DESC' - один из двух методов сортировки
     *
     * @param array
     * @return array
     * @todo: вынести в отдельный класс, внести ясность в синтаксис
     */
    protected static function makeSqlFromParams($params)
    {
        // Аргументы для подстановки в маркеры SQL запроса.
        // Фактически, это константные данные SQL-запроса.
        $sql_store = array('args' => array(),
                           'where' => '',
                           'join' => '',
                           'what' => ' * ',
                           'limit' => '',
                           'order' => '',
                           'group' => ''
                           );

        // what
        if (!empty($params['what'])) {
            $what_sql = '';

            if (is_array($params['what'])) {
                foreach ($params['what'] as $sql_key => $args_value) {
                    foreach ($args_value as $value) {
                        $sql_store['args'][] = is_object($value) ? $value->getValue() : $value;
                    }

                    $what_sql .= ' ' . $sql_key . ' ';
                }
            } else {
                $what_sql = trim($params['what']);
            }

            $sql_store['what'] = $what_sql !== '' ? ' ' . $what_sql . ' ' : $sql_store['what'];
        }

        // where-условие
        if (!empty($params['where'])) {
            $where_sql = '';

            if (is_array($params['where'])) {
                foreach ($params['where'] as $sql_key => $args_value) {
                    foreach ($args_value as $value) {
                        $sql_store['args'][] = is_object($value) ? $value->getValue() : $value;
                    }

                    $where_sql .= ' '.$sql_key.' ';
                }
            } else {
                $where_sql = trim($params['where']);
            }

            $sql_store['where'] = $where_sql !== null && $where_sql !== '' ? ' WHERE ' . $where_sql : '';
        }

        // join
        if (!empty($params['join'])) {
            $join_array = array();

            foreach ($params['join'] as $join) {
                $join_array[] = ' '.$join[0].' '.$join[1].' ON '.$join[2].' ';
            }

            $sql_store['join'] = implode('', $join_array);
        }

        // limit
        if (!empty($params['limit']) && is_array($params['limit'])) {
            $sql_store['limit'] = isset($params['limit']['start']) && Krugozor_Static_Numeric::is_decimal($params['limit']['start'], true)
                                  ? ' LIMIT '.$params['limit']['start'].
                                    (isset($params['limit']['stop']) && Krugozor_Static_Numeric::is_decimal($params['limit']['stop'], true)
                                     ? ', '.$params['limit']['stop']
                                     : ''
                                    )
                                  : '';
        }

        // order
        if (!empty($params['order'])) {
            $order_sql = '';

            foreach ($params['order'] as $field => $method) {
                // Определяем, что из себя представляет order-параметр:
                // Если order имеет вид типа table.col, то преобразуем этот параметр в `table`.`col`,
                // если order имеет вид типа col, то преобразуем к виду `col`.
                $temp = explode('.', $field);

                if (count($temp) > 1) {
                    $field = '`'.$temp[0].'`.`'.$temp[1].'`';
                } else {
                    $field = '`'.$field.'`';
                }

                $order_sql .= $field.' '.$method.', ';
            }

            $order_sql = rtrim($order_sql, ', ');

            $sql_store['order'] = ' ORDER BY '.$order_sql;
        }

        // group
        // todo: сделать возможность группировать по полям вида таблица.поле как в order?
        if (isset($params['group'])) {
            $group_sql = '';

            foreach ($params['group'] as $field => $method) {
                $temp = explode('.', $field);

                if (count($temp) > 1) {
                    $field = '`' . $temp[0] . '`.`' . $temp[1] . '`';
                } else {
                    $field = '`' . $field . '`';
                }

                $group_sql .= $field . ' ' . $method . ', ';
            }

            $group_sql = rtrim($group_sql, ', ');

            $sql_store['group'] = ' GROUP BY ' . $group_sql;
        }

        return $sql_store;
    }

    /**
     * Возвращает массив объектов, содержащих метаданные полей текущей таблицы.
     *
     * @param void
     * @return array
     */
    protected function getTableMetada()
    {
        if (!isset(self::$list_fields[$this->getTableName()])) {
            $result = $this->getDb()->query('SELECT * FROM ?f ORDER BY 1 ASC LIMIT 1', $this->getTableName());
            $finfo = $result->getResult()->fetch_fields();

            while ($obj = $result->getResult()->fetch_field()) {
                self::$list_fields[$this->getTableName()][$obj->name] = $obj;
            }
        }

        return self::$list_fields[$this->getTableName()];
    }

    /**
     * Возвращает класс модели на основании двух параметров - имени модуля и модели.
     *
     * @param string $module имя модуля
     * @param string $model имя модели
     */
    private final function getModelClassNameByParts($module, $model)
    {
        return 'Krugozor_Module_' . Krugozor_Static_String::formatToCamelCaseStyle($module) .
                '_Model_' . Krugozor_Static_String::formatToCamelCaseStyle($model);
    }

    /**
     * Записывает во внутреннее представление объекта имя модуля и модели.
     *
     * @param void
     * @return void
     */
    private final function injectModelInfo()
    {
        list(,,$this->module_name,,$this->model_name) = explode('_', get_class($this));
    }
}