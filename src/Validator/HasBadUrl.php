<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка на наличия URL-адреса в значении $this->value.
 * Подразумевается, что $this->value - это некий текст, введенный пользователем.
 */
class HasBadUrl extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'BAD_URL_IN_TEXT';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        preg_match_all(Strings::$url_pattern_search, $this->value, $matches);

        if (!empty($matches[0])) {
            $this->error_params = array('url' => implode(', ', $matches[0]));

            return false;
        }

        return true;
    }
}