<?php

namespace Krugozor\Framework\Module\Common\Model;

use Krugozor\Framework\Model;
use Krugozor\Framework\Statical\Strings;

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
class Fake extends Model
{
    /**
     * Получение и установка свойств объекта через вызов магического метода вида:
     *
     * $model->(get|set)PropertyName($prop);
     *
     * @param string $method_name
     * @param $argument
     * @return $this|mixed
     */
    public function __call(string $method_name, $argument)
    {
        $args = preg_split(Strings::$pattern_search_method_name, $method_name);

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
     * $excluded_keys в контексте facke-модели не используется.
     *
     * @param array|\Krugozor\Framework\Cover\CoverArray $data
     * @param array $excluded_keys
     * @return Model
     */
    public function setData($data, array $excluded_keys = array()): Model
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function __set(string $key, $value)
    {
        $this->data[$key] = $value;
    }
}