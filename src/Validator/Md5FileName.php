<?php

/**
 * Возвращает true, если значение является корректной
 * строкой - 32-символьным именем файла на основе хэша md5.
 */
class Krugozor_Validator_Md5FileName extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_MD5_FILE_NAME';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        return preg_match('~^[0-9a-f]{32}\.[a-z]{2,4}$~i', $this->value);
    }
}