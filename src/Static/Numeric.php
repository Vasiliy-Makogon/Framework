<?php

/**
 * Класс-обертка над функциями для работы с числами.
 */
class Krugozor_Static_Numeric
{
    /**
     * Паттерн для поиска десятичных знаковых чисел в тексте.
     *
     * @var string
     */
    public static $pattern_sign_search = '~([+\-]?[0-9]+)~';

    /**
     * Паттерн для точного определения десятичного знакового числа.
     *
     * @var string
     */
    protected static $pattern_sign = '~^([+\-]?[0-9]+)$~';

    /**
     * Паттерн для точного определения десятичного беззнакового числа.
     *
     * @var string
     */
    protected static $pattern_unsigned = '~^([0-9]+)$~';

    /**
     * Проверяет, является ли значение числом.
     *
     * @param mixed $value - проверяемое значение
     * @param boolean если $signed в true, то число проверяется как знаковое,
     *                иначе - как беззнаковое.
     * @return bool
     */
    public static function is_decimal($value, $signed = false)
    {
        $pattern = $signed ? self::$pattern_sign : self::$pattern_unsigned;

        if (preg_match($pattern, strval($value), $matches) !== 0) {
            return $matches;
        }

        return false;
    }
}