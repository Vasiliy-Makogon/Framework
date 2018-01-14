<?php

/**
 * Проверка значения на корректный email-адрес.
 */
class Krugozor_Validator_Email extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_STRING_EMAIL';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        return Krugozor_Static_String::isEmail($this->value);
    }
}