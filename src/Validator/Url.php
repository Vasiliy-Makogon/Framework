<?php

/**
 * Проверка значения на корректный URL-адрес.
 */
class Krugozor_Validator_Url extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_STRING_URL';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        return Krugozor_Static_String::isUrl($this->value);
    }
}