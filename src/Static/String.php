<?php

/**
 * Класс-обертка над функциями для работы со строками.
 */
class Krugozor_Static_String
{
    /**
     * Паттерн для поиска URL адреса в тексте.
     *
     * @var string
     */
    public static $url_pattern_search = "#
        (?:
            (?:https?://(?:www\.)?|(?:www\.))
            (?:\S+)
            (?::[0-9]+)?
            (?:/\S+)*
            [^\s.,'\"]*
        )
        #uxi";

    /**
     * Паттерн для точного определения URL адреса.
     *
     * @var string
     */
    public static $url_pattern = "#^
        (?:
            https?://(?:www\.)?
            (\S+)
            (:[0-9]+)?
            (/\S+)?
            [^\s.,'\"]
        )
        $#uxi";

    /**
     * Паттерн для точного определения email адреса.
     *
     * @var string
     */
    public static $email_pattern = "/^[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+
                                    (?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@
                                    (?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+
                                    (?:[a-zA-Z]{2,6})$/uix";
    /**
     * Паттерн для поиска email адреса в тексте.
     *
     * @var string
     */
    public static $email_pattern_search = "/[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+
                                          (?:\.[a-zA-Z0-9!#$%&'*+\/=?^_`{|}~-]+)*@
                                          (?:[a-zA-Z0-9](?:[a-zA-Z0-9-]*[a-zA-Z0-9])?\.)+
                                          (?:[a-zA-Z]{2,6})/uix";

    /**
     * Паттерн для вычисления действия со свойством (set- или get-) по имени виртуального метода.
     * См. метод self::camelCaseToProperty()
     *
     * @var string
     */
    public static $pattern_search_method_name = '/(?<=\w)(?=[A-Z])/';

    /**
     * Преобразование из строки из CamelCase вида в camel_case.
     *
     * @param string
     * @param string
     */
    public static function camelCaseToProperty($method_name)
    {
        $args = preg_split(self::$pattern_search_method_name, $method_name);

        return strtolower(implode('_', $args));
    }

    /**
     * Возвращает true, если значение пусто - содержит пустую строку,
     * false или null. Применяется для валидаторов, для проверки
     * данных из REQUEST, когда 0 нельзя трактовать как false.
     *
     * @param string
     * @return boolean
     */
    public static function isEmpty($string)
    {
        if (!is_numeric($string)) {
            return empty($string);
        }

        return false;
    }

    /**
     * Проверяет, является ли строка URL адресом.
     *
     * @param string $string
     * @return boolean
     */
    public static function isUrl($string)
    {
        return preg_match(self::$url_pattern, $string);
    }

    /**
     * Проверяет, является ли строка email адресом.
     *
     * @param string $string
     * @return boolean
     */
    public static function isEmail($string)
    {
        return preg_match(self::$email_pattern, $string);
    }

    /**
     * Возвращает уникальную строку длинной $length.
     * Если $length не задана, то длинной в 32 символа.
     *
     * @param null|int
     * @return string
     */
    public static function getUnique($length = null)
    {
        $length = intval($length);

        if (!$length || $length > 32) {
            $length = 32;
        }

        return substr(md5(microtime() . rand(1, 10000000)), 0, $length);
    }

    /**
     * Создает строку-сообщение для вывода пользователю.
     * Принимает языковой шаблон $str и массив аргументов
     * вида 'key' => 'value' и заменяет в шаблоне все вставки типа
     * {var_name} на значения из массива аргументов с соответствующими ключами.
     *
     * @param string строка с метками вида {var_name}
     * @param array ассоциативный массив аргументов
     * @param bool $htmlspecialchars применять ли для значений htmlspecialchars()
     * @return string
     */
    public static function createMessageFromParams($str, $args, $htmlspecialchars = true)
    {
        foreach ($args as $key => $value) {
            $value = $htmlspecialchars ? htmlspecialchars($value, ENT_QUOTES) : $value;
            $str = str_replace('{' . $key . '}', $value, $str);
        }

        return $str;
    }

    /**
     * Форматирует строку $string в CamelCase-стиль,
     * включая первый символ первого слова.
     *
     * @param string
     * @return string
     */
    public static function formatToCamelCaseStyle($string)
    {
        $parts = preg_split('~-|_~', $string);

        if (count($parts) <= 1) {
            return ucfirst($string);
        }

        $str = '';

        foreach ($parts as $part) {
            $str .= ucfirst($part);
        }

        return $str;
    }

    /**
     * Удаляет в начале и конце строки знаки пунктуации.
     *
     * @param $value string
     * @return string
     */
    public static function trimPunctuation($value)
    {
        return trim($value, '.,!?:; ');
    }
}