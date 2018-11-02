<?php

namespace Krugozor\Framework\Validator;

/**
 * Возвращает true, если значение "не пусто", т.е. попадает под конструкцию !empty
 */
class IsNotEmpty extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'EMPTY_VALUE';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return !empty($this->value);
    }
}