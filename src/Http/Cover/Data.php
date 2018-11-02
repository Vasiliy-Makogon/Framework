<?php

namespace Krugozor\Framework\Http\Cover;

use Krugozor\Cover\CoverArray;

/**
 * Оболочка над GPCR массивами.
 */
abstract class Data extends CoverArray
{
    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        parent::__construct(self::clearData($data));
    }

    /**
     * Очищает массив от пробелов.
     *
     * @param array $in
     * @return array
     */
    private static function clearData(array &$in): array
    {
        if ($in && is_array($in)) {
            foreach ($in as $key => $value) {
                if (is_array($value)) {
                    self::clearData($in[$key]);
                } else {
                    $in[$key] = trim($value);
                }
            }
        }

        return $in;
    }
}