<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Возвращает true, если значение является корректной
 * строкой - 32-символьным именем файла на основе хэша md5.
 */
class Md5FileName extends ValidatorAbstract
{
    protected $error_key = 'INVALID_MD5_FILE_NAME';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value)) {
            return true;
        }

        return (bool) preg_match('~^[0-9a-f]{32}\.[a-z]{2,4}$~i', $this->value);
    }
}