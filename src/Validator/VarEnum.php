<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка значения на наличие в множестве $this->enum.
 */
class VarEnum extends ValidatorAbstract
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
     * @return VarEnum
     */
    public function setEnum(array $enum): self
    {
        $this->enum = $enum;

        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value)) {
            return true;
        }

        return in_array($this->value, $this->enum);
    }
}