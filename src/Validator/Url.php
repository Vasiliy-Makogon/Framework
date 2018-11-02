<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка значения на корректный URL-адрес.
 */
class Url extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'INVALID_STRING_URL';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value) || $this->value == 'http://') {
            return true;
        }

        return Strings::isUrl($this->value);
    }
}