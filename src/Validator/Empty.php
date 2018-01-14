<?php

/**
 * Проверка значения на пустоту. Обертка над стандартной конструкцией !empty().
 */
class Krugozor_Validator_Empty extends Krugozor_Validator_Abstract
{
    protected $error_key = 'EMPTY_VALUE';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        return !empty($this->value);
    }
}