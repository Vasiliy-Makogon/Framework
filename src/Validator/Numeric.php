<?php

/**
 * Проверка значения на число с помощью is_numeric().
 */
class Krugozor_Validator_Numeric extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_NUMERIC';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        return is_numeric($this->value);
    }
}