<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка значения на число с помощью is_numeric().
 */
class Numeric extends ValidatorAbstract
{
    protected $error_key = 'INVALID_NUMERIC';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value)) {
            return true;
        }

        return is_numeric($this->value);
    }
}