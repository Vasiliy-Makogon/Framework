<?php

/**
 * Возвращает true если значение не является null, false или пустой строкой.
 * false в противном случае.
 */
class Krugozor_Validator_IsNotEmpty extends Krugozor_Validator_Abstract
{
    protected $error_key = 'EMPTY_VALUE';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        return !Krugozor_Static_String::isEmpty($this->value);
    }
}