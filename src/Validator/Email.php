<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка значения на корректный email-адрес.
 */
class Email extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'INVALID_STRING_EMAIL';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value)) {
            return true;
        }

        return Strings::isEmail($this->value);
    }
}