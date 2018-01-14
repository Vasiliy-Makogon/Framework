<?php

/**
 * Проверка значения на наличие в множестве $this->enum.
 */
class Krugozor_Validator_VarEnum extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INCORRECT_VALUE';

    /**
     * Массив, который проходит проверку на наличие в нем значения $this->value
     *
     * @var array
     */
    private $enum = array();

    /**
     * @param array $enum
     * @return Krugozor_Validator_VarEnum
     */
    public function setEnum(array $enum)
    {
        $this->enum = (array)$enum;

        return $this;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        return in_array($this->value, $this->enum);
    }
}