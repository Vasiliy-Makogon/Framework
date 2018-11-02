<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Возвращает true если значение не является null, false или пустой строкой.
 * false в противном случае.
 */
class IsNotEmptyString extends ValidatorAbstract
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
        return !Strings::isEmpty($this->value);
    }
}