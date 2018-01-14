<?php

/**
 * Оболочка над GPCR массивами.
 */
abstract class Krugozor_Http_Cover_Data extends Krugozor_Cover_Array
{
    public function __construct(array $data = array())
    {
        parent::__construct(self::clearData($data));
    }

    /**
     * Очищает массив от пробелов и слэшей.
     *
     * @param array
     * @return array
     */
    private static function clearData(&$in)
    {
        if ($in && is_array($in)) {
            foreach ($in as $key => $value) {
                if (is_array($value)) {
                    self::clearData($in[$key]);
                } else {
                    $value = trim($value);

                    if (get_magic_quotes_gpc()) {
                        $value = stripslashes($value);
                    }

                    $in[$key] = $value;
                }
            }
        }

        return $in;
    }
}