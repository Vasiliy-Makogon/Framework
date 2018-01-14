<?php
/**
 * Fake-модель.
 * Предназначена для ситуаций, когда в полученном результате SQL-запроса
 * присутствуют поля данных, полученные не из конкретных существующих таблиц, а в результате вычислений:
 *
 *  SELECT COUNT(*) as `advert_count` ...
 *  SELECT 1 + 1 as `number` ...
 *
 *  Эти данные траслируются в Fake модель и доступны в ней с помощью виртуальных методов,
 *  аналогичных базовым моделям:
 *
 *  $common_fake->getAdvertCount()
 *  $common_fake->getNumber()
 */
class Krugozor_Module_Common_Model_Fake extends Krugozor_Model
{
    /**
     * Получение и установка свойств объекта через вызов магического метода вида:
     *
     * $model->(get|set)PropertyName($prop);
     *
     * @see __call
     * @return mixed
     */
    public function __call($method_name, $argument)
    {
        $args = preg_split(Krugozor_Static_String::$pattern_search_method_name, $method_name);

        $action = array_shift($args);

        $property_name = strtolower(implode('_', $args));

        switch ($action) {
            case 'get':
                return $this->$property_name;

            case 'set':
                $this->$property_name = $argument[0];
                return $this;
        }
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Model::setData()
     *
     * $excluded_keys в контексте facke-модели не используется
     */
    public function setData($data, array $excluded_keys=array())
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Model::__set()
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }
}