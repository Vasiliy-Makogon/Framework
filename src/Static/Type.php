<?php

class Krugozor_Static_Type
{
    /**
     * Приведение к типу $type значение $value.
     *
     * @param mixed $value значение
     * @param string $type тип, к которому будет приведено значение
     * @return mixed
     * @static
     */
    public static function sanitizeValue($value, $type)
    {
        switch ($type) {
            case 'decimal':
                if (preg_match(Krugozor_Static_Numeric::$pattern_sign_search, $value, $matches) !== 0) {
                    return $matches[0];
                }

                return 0;

            case 'string':
                return (string)$value;

            case 'bool':
            case 'boolean':
                return (bool)$value;

            case 'array':
                return (array)$value;

            default:
                trigger_error(__METHOD__ . ': Недопустимый тип ' . $type);
                break;
        }

        return $value;
    }
}