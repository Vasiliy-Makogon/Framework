<?php
abstract class Krugozor_Model extends Krugozor_Cover_Abstract_Simple
{
    /**
     * Карта атрибутов модели.
     * Перечисляются в дочерних классах в виде массивов следующего содержания:
     *
     * 'first_name' => array('type' => 'Krugozor_Module_..._Type_...'
     *                       'db_element' => true,
     *                       'db_field_name' => 'user_first_name',
     *                       'default_value' => null,
     *                       'validators' => array(
     *                           'Decimal' => array('signed' => false), ...)
     *                       )
     *
     * Допустимые свойства и их возможные значения:
     *
     * type          Тип данных свойства. Тип указывается только для "сложных", не скалярных типов (объектов),
     *               например, для таких, как объект Krugozor_Type_Datetime,
     *               Krugozor_Type_Email и т.д. Если тип не указан, значит, это скаляр.
     *
     * db_element    [true|false] должно ли это свойство записываться в БД.
     *               В большинстве случаев это свойство устанавливается в true. Исключения составляют
     *               какие-то "вспомогательные" свойства объекта, которые допустимо иметь в качестве
     *               членов класса, но записывать в БД не нужно (для них в таблицах просто нет полей).
     *               Например, свойство ID (Primary Key) для каждой таблицы имеет значение false по
     *               причине того, что никогда не пишется в таблицу, а является лишь указателем
     *               на запись, т.е. фактически является "вспомогательным" в данной терминологии.
     *
     * db_field_name Имя поля таблицы данных, ассоциируемое с данным свойством класса.
     *
     * default_value Значение свойства по умолчанию, при инстанцировании объекта.
     *               Данный параметр никак не связан со значением DEFAULT SQL-описания таблицы данных.
     *
     * validators    Массив валидаторов, которые должны быть применены к свойству при присвоении ему значения.
     *               Массив имеет вид
     *                   'ModuleName/ValidatorName' => array('length' => value[, ...])
     *               где ключ - строка, описывающая местонахождение валидатора ValidatorName в модуле ModuleName,
     *               а значение - массив параметров валидатора. Для каждого параметра в валидаторе должен быть
     *               реализован set-метод. Например, для параметра `length` из примера, в валидаторе должен быть
     *               реализован set-метод setLength($value), который установит свойство `length` валидатора в
     *               значение value.
     *               Ошибки, возникшие впроцессе валидации, доступны через соответствующие методы данного класса.
     *
     * record_once   Если true, то данное свойство модели записывается в базу и больше не может быть перезаписано через
     *               метод Krugozor_Mapper::saveModel(), ПРИ УСЛОВИИ, что данные добавляются
     *               через метод Krugozor_Model::setData().
     *               Данный флаг нужно устанавливать для полей, которые должны писаться в базу единожды.
     *               Например поле даты, символизирующее о дате создания записи.
     *               Если для свойства установлен данный флаг, то при вызове метода setData(array('mykey' => 'val'))
     *               значение 'val' не будет присвоено свойству модели 'mykey', в 'mykey' будет установлен `оригинальный`
     *               вариант значения, т.е. тот, который существует в объекте.
     *
     * @var array
     */
    protected static $model_attributes = array();

    /**
     * Оригинальные свойства объекта. Аналог $this->data, но данный объект наполняется один раз - при вызове
     * метода setData() объекта модели и после не меняется.
     *
     * Предназначен для определения тех свойств объекта, которые были изменены и должны быть подставлены
     * в SQL запрос на сохранение в методе маппера Krugozor_Mapper::saveModel().
     *
     * @var Krugozor_Model_Track
     */
    protected $track;

    /**
     * Префикс имен полей таблицы.
     * Исторически все поля таблиц моделей, за исключением поля id, именуются с однотипными префиксами,
     * означающим, к какой таблице относится поле.
     * Например: user_name (таблица user), group_type (таблица group) и т.д.
     * Данное свойство можно указать в виде пустой строки.
     *
     * @var string
     */
    protected static $db_field_prefix;

    /**
     * Многомерный массив сообщений об ошибках валидации свойств.
     * Заполняется сообщениями, посупающими из валидаторов при присвоении объекту
     * значений, не удовлетворяющих описанным валидаторам в self::$model_attributes.
     *
     * @var array
     */
    protected $validate_errors = array();

    /**
     * Менеджер мэпперов.
     *
     * @var Krugozor_Mapper_Manager
     */
    private $mapperManager;

    public function __construct()
    {
        $this->track = new Krugozor_Model_Track();
    }

    /**
     * Принимает объект хранилища инстанцированных мепперов Krugozor_Mapper_Manager.
     *
     * @param Krugozor_Mapper_Manager $mapperManager
     * @return Krugozor_Model
     */
    public final function setMapperManager(Krugozor_Mapper_Manager $mapperManager)
    {
        $this->mapperManager = $mapperManager;

        return $this;
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
     * Принимает потенциальный массив свойств объекта (вида "имя_свойства" => "значение")
     * и анализируя ключи, вызывает виртуальные set-методы, через которые значения
     * присваиваются объекту. Если в объект подается ключ, имя которого не найдено
     * в массиве известных аттрибутов модели self::$model_attributes, то такое
     * присваивание будет проигнорировано без каких-либо ошибок. Это поведение сделано для того, что бы любой
     * мусор из POST-запроса не мог вызывать какие-либо ошибки по этому поводу или исключительные ситуации.
     *
     * @param array $data
     * @param array $excluded_keys ключи свойств модели, которые не будут перезаписаны данными из $data.
     *              Если объект модели, для которого вызывается setData() уже содержит данные, то все свойства с
     *              ключами из $excluded_keys сохранят свои оригинальные значения.
     * @return Krugozor_Model
     */
    public function setData($data, array $excluded_keys=array())
    {
        // Для проверки $data на наличие свойств с ключами через array_key_exists
        // выяснить насколько это нужно
        // или оставить, т.к. в некотрых случаях передается именно объект запроса
        if (is_object($data) && $data instanceof Krugozor_Cover_Array) {
            $data = $data->getDataAsArray();
        }

        // Из POST-запроса приходит массив вида:
        // 'id' => string '1344'
        // 'active' => string '1'
        // 'name' => string 'Vasya'
        // ...
        // а из базы получаем массив с префиксами полей:
        // 'id' => string '1344'
        // 'user_active' => string '1'
        // 'user_name' => string 'Vasya'
        // ...
        // Цикл ниже приводит ключи массива $data в нормальный вид - без префикса полей.
        foreach ($data as $key => $value) {
            unset($data[$key]);
            $data[self::getPropertyNameWithountPrefix($key)] = $value;
        }

        foreach (self::getModelsPropertiesSettings() as $key => $props) {
            // Если нужно исключить присвоение свойству $key значения из $data[$key] (см. $excluded_keys)
            // или
            // свойство имеет флаг record_once, то значением свойства $key будет текущее значение объекта.
            if (in_array($key, $excluded_keys) || (self::isPropertyRecordOnce($key) && $this->track->count() > 0)) {
                continue;
            } else {
                $method_name = $this->getMethodNameByKeyWithPrefix($key, 'set');

                // Если в массиве извне есть пара ключ => значение, значит
                // необходимое значение нам передали (например, из POST-запроса)...
                if (array_key_exists($key, $data)) {
                    $value = $data[$key];
                }
                // .. иначе значение не пришло.
                // Мы это значение получим либо из track, а если его там нет - возьмем то,
                // что объявлено по-умолчанию.
                else {
                    $value = array_key_exists($key, $this->track->getData())
                             ? $this->track->$key
                             : self::getPropertyDefaultValue($key);
                }

                $this->$method_name($value);
            }
        }

        // Если идет создание нового объекта, то заполняем $this->track
        // Возможно данный код придется переписать.
        // Он даст логическую ошибку на ситуации, когда setData() будет вызван
        // более одного раза на пустом объекте. Во второй раз данные не будут записаны в track.
        // Проверочный код:
        /*
           $object = $this->getMapper('User/User')->createModel();
           $object->setData(array('id'=>0, 'unique_cookie_id' => 'value'));
           var_dump($object->getTrack()->email);
           echo "\n\n--------\n\n";
           $object->setData(array('email' => 'info@mirgorod.ru'));
           var_dump($object->getTrack()->email);
        */
        if ($this->track->count() === 0) {
            $this->track->setData($this->data);
        }

        return $this;
    }

    /**
     * Возвращает $this->track
     *
     * @return Krugozor_Model_Track
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * Возвращает префикс имен полей таблицы.
     *
     * @param void
     * @return string
     */
    public function getDbFieldPrefix()
    {
        return static::$db_field_prefix;
    }

    /**
     * Возвращает имя поля таблицы для свойства $property_name.
     *
     * @param string $property_name имя сваойства
     * @return string|null
     */
    public static function getPropertyFieldName($property_name)
    {
        return isset(static::$model_attributes[$property_name])
               ? static::$model_attributes[$property_name]['db_field_name']
               : null;
    }

    /**
     * Возвращает значение по-умолчанию свойства $property_name.
     *
     * @param string $property_name имя сваойства
     * @return mixed|null
     */
    public static function getPropertyDefaultValue($property_name)
    {
        return isset(static::$model_attributes[$property_name]) &&
               isset(static::$model_attributes[$property_name]['default_value'])
               ? static::$model_attributes[$property_name]['default_value']
               : null;
    }

    /**
     * Возвращает значение, указывающее, пишется ли данное свойство модели в СУБД.
     *
     * @param string $property_name имя сваойства
     * @return bool
     */
    public static function getPropertyDbElement($property_name)
    {
        return isset(static::$model_attributes[$property_name])
               ? static::$model_attributes[$property_name]['db_element']
               : null;
    }

    /**
     * Возвращает тип свойства $property_name.
     *
     * @param string $property_name имя сваойства
     * @return mixed|null
     */
    public static function getPropertyType($property_name)
    {
        return isset(static::$model_attributes[$property_name]) && !empty(static::$model_attributes[$property_name]['type'])
               ? static::$model_attributes[$property_name]['type']
               : null;
    }

    /**
     * Возвращает true, если свойство модели записывается в объект единожды.
     *
     * @param string $property_name
     * @return bool
     */
    public static function isPropertyRecordOnce($property_name)
    {
        return isset(static::$model_attributes[$property_name]) &&
               !empty(static::$model_attributes[$property_name]['record_once']) &&
               static::$model_attributes[$property_name]['record_once'] == true;
    }

    /**
     * Возвращает массив настроек свойств модели.
     *
     * @param void
     * @return array
     */
    public function getModelsPropertiesSettings()
    {
        return static::$model_attributes;
    }

    /**
     * Получает имя свойства без префикса static::$db_field_prefix.
     *
     * @param string $property_name
     * @return string
     */
    protected function getPropertyNameWithountPrefix($property_name)
    {
        return preg_replace('~^(?:' . static::$db_field_prefix . '_)*([a-z0-9_]+)$~', '$1', $property_name);
    }

    /**
     * Получает имя set- или get- метода для свойства объекта с именем $property_name.
     * Имя свойства $property_name может подаваться с приставкой $this->db_field_prefix
     * или без неё, т.е. методы с разными вызовами:
     *
     * ->getMethodNameByKeyWithPrefix('user_name', 'set');
     * ->getMethodNameByKeyWithPrefix('name', 'set');
     *
     * возвратят одинаковый результат - имя метода setUserName()
     *
     * @param string $property_name имя свойства объекта
     * @param string $action set|get действие метода
     * @return null|string имя get- или set- метода
     */
    public function getMethodNameByKeyWithPrefix($property_name, $action = 'set')
    {
        $key = preg_replace('~^' . static::$db_field_prefix . '_([a-z0-9_]+)$~', '$1', $property_name);

        if (isset(static::$model_attributes[$key])) {
            $args = explode('_', $key);

            $count = count($args);

            for ($i = 0; $i < $count; $i++) {
                $args[$i][0] = strtoupper($args[$i][0]);
            }

            $key = implode('', $args);

            if (!in_array($action, array('set', 'get'))) {
                trigger_error(__METHOD__ . ': Указан некорректный action ' . $action, E_USER_WARNING);

                return null;
            }

            return $action . $key;
        }

        return null;
    }

    /**
    * Устанавливает значение $value для свойства $key объека.
    *
    * @param string $key имя свойства объекта
    * @param mixed $value значение свойства объекта
    * @return Krugozor_Model
    */
    public function __set($key, $value)
    {
        if (!isset(static::$model_attributes[$key])) {
            trigger_error(__METHOD__ . ': Свойство ' . $key . ' не принадлежит модели ' . get_class($this), E_USER_WARNING);

            return $this;
        }

        // В карте описания свойств модели static::$model_attributes указаны валидаторы,
        // которыми необходимо валидировать значение $value.
        // Модель принимает любые скалярные данные, даже ошибочные.
        // Валидация в модели носит лишь уведомительный характер.
        // Принимать решение, что делать с ошибочной моделью должен слой,
        // оперирующий с этой моделью.
        if (isset(static::$model_attributes[$key]['validators'])) {
            // Если в объекте модели уже содержится информация об ошибочном заполнении
            // данного свойства, то эту информацию необходимо удалить, т.к. идет присвоение нового
            // значения и старая информация об ошибках уже не актуальна.
            if (isset($this->validate_errors[$key])) {
                unset($this->validate_errors[$key]);
            }

            foreach (static::$model_attributes[$key]['validators'] as $validator_path => $params) {
                $validator_info = explode('/', $validator_path);

                if (count($validator_info) == 1) {
                    $validator_class_name = 'Krugozor_Validator_' . $validator_info[0];
                } else if (count($validator_info) == 2) {
                    $validator_class_name = 'Krugozor_Module_' . $validator_info[0] . '_Validator_' . $validator_info[1];
                } else {
                    throw new UnexpectedValueException(__METHOD__ . ": Указан некоректный путь к валидатору $validator_path");
                }

                if (class_exists($validator_class_name)) {
                    // $value может быть либо объектом - собственным типом данных фреймворка, либо скаляром.
                    $value = is_object($value) && $value instanceof Krugozor_Type_Interface
                             ? $value->getValue()
                             : $value;

                    $validator = new $validator_class_name($value);

                    foreach ($params as $validator_criteria => $criteria_value) {
                        $method = 'set' . Krugozor_Static_String::formatToCamelCaseStyle($validator_criteria);

                        if (method_exists($validator, $method)) {
                            $validator->$method($criteria_value);
                        } else {
                            throw new BadMethodCallException(
                                __METHOD__ . ': Вызов неизвестного метода валидатора ' . $validator_class_name . '::' . $method
                            );
                        }
                    }

                    // Возникли ошибки валидации, помещаем их в общее хранилище.
                    if (!$validator->validate()) {
                        $this->validate_errors[$key][] = $validator->getError();

                        if ($validator->getBreak()) {
                            break;
                        }
                    }
                }
            }
        }

        $this->setValueWithTransformation($key, $value);

        return $this;
    }

    /**
     * Получение и установка свойств объекта через вызов магического метода вида:
     *
     * $model->(get|set)PropertyName($prop);
     *
     * Внимание! Явно объявленные методы (get|set)PropertyName() как protected или private
     * могут привести к непредсказуемым результатам.
     *
     * @see __call
     * @return mixed
     */
    public function __call($method_name, $argument)
    {
        $args = preg_split(Krugozor_Static_String::$pattern_search_method_name, $method_name);

        $action = array_shift($args);

        $property_name = strtolower(implode('_', $args));

        if (!isset(static::$model_attributes[$property_name])) {
            throw new BadMethodCallException(
                __METHOD__ . ': Вызов неизвестного метода ' . get_class($this) . '::' . $method_name
            );
        }

        switch ($action) {
            case 'get':
                return $this->$property_name;

            case 'set':
                if (count($argument) == 0) {
                    throw new RuntimeException(
                        __METHOD__ . ': вызов метода ' . get_class($this) . ':' . $method_name . ' без указания аргумента'
                    );
                }

                $this->$property_name = $argument[0];

                $has_errors = isset($this->validate_errors[$property_name]);

                $explicit_method = '_' . $method_name;

                // Смотрим, имеется ли в классе явно объявленный set-метод (с префиксом "_") для
                // данного свойства и имеются ли ошибки валидации.
                // Если метод явно объявлен, а ошибок валидации нет, то применяем метод
                // для текущего состояния свойства.
                // Данные методы, с префиксом '_' в модели нужны для ситуаций, когда при присвоении объекту значений
                // необходимо обработать значение с помощью какой-либо логики.
                // Как пример, можно посмотреть метод Krugozor_Module_User_Model_User::_setUrl
                if (method_exists($this, $explicit_method) && !$has_errors) {
                   $this->data[$property_name] = $this->$explicit_method($this->$property_name);
                }

                return $this;
        }
    }

    /**
     * Возвращает ошибки валидации модели.
     *
     * @param void
     * @param boolean $add_model_prefix - true, если ключи нужно возвращать с префиксом self::$db_field_prefix,
     *                                    false - в ином случае.
     * @return array
     */
    public function getValidateErrors($add_model_prefix = false)
    {
        if ($add_model_prefix) {
            foreach ($this->validate_errors as $key => $value) {
                unset($this->validate_errors[$key]);

                $this->validate_errors[static::$db_field_prefix . '_' . $key] = $value;
            }
        }

        return $this->validate_errors;
    }

    /**
     * Возвращает ошибки валидации свойства $key.
     *
     * @param string $key имя проверяемого свойства
     * @return null|array информация об ошибке
     */
    public function getValidateErrorsByKey($key)
    {
        if (isset($this->validate_errors[$key])) {
            return $this->validate_errors[$key];
        }

        return null;
    }

    /**
     * Явный метод setId(), предупреждающий затирание явно существующего ID текущего объекта.
     *
     * @param int $id
     * @return boolean
     * @throws LogicException
     */
    public function setId($id)
    {
        if (!empty($this->data['id']) && $this->data['id'] != $id) {
            throw new LogicException(
                 __METHOD__ . ': Попытка переопределить значение ID объекта модели ' . get_class($this) . ' значением ' . $id
            );
        }

        $this->id = $id;

        return true;
    }

    /**
     * Устанавливает для свойства объекта $key значение $value
     * в соответствии с картой описания свойств static::$model_attributes[$key].
     *
     * @param string $key имя свойства объекта
     * @param mixed $value значение свойства объекта
     * @return void
     * @throws RuntimeException
     */
    private function setValueWithTransformation($key, $value)
    {
        // Тип свойства не указан в карте описания свойств модели.
        // Значит, работаем со скалярным типом данных и присваеваем
        // "как есть" значение $value свойству $key.
        if (!isset(static::$model_attributes[$key]['type'])) {
            $this->data[$key] = $value;
        } else {
            // Если $value - объект, производный от указанного в карте
            // описания свойств модели, то никаких преобразований с $value не делаем.
            if (is_object($value) && $value instanceof static::$model_attributes[$key]['type']) {
                $this->data[$key] = $value;
            }
            // Если $value - скалярное значение, значит, его необходимо
            // преобразовать в указанный в карте модели объект.
            // Для этого значение $value необходимо передать в конструктор
            // указанного в карте описания свойств модели класса.
            else {
                if (!class_exists(static::$model_attributes[$key]['type'])) {
                    throw new RuntimeException(
                        __METHOD__ . ': Не найден класс типа ' . static::$model_attributes[$key]['type']
                    );
                }

                // Если в объекте типа будет выброшено исключение UnexpectedValueException,
                // значит, объект создавать не нужно, а свойству модели $key необходимо
                // присвоить значение null.
                // Эта ситуация возможна пока лишь для объекта Krugozor_Type_Datetime
                try {
                    $this->data[$key] = new static::$model_attributes[$key]['type']($value);
                } catch (UnexpectedValueException $e) {
                    $this->data[$key] = null;
                }
            }
        }
    }
}