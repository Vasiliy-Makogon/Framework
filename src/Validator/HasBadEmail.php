<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка на наличия email-адреса в значении $this->value.
 * Подразумевается, что $this->value - это некий текст, введенный пользователем.
 */
class HasBadEmail extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'BAD_EMAIL_IN_TEXT';

    /**
     * @return bool
     */
    public function validate(): bool
    {
        preg_match_all(Strings::$email_pattern_search, $this->value, $matches);

        if (!empty($matches[0])) {
            $this->error_params = array('email' => implode(', ', $matches[0]));

            return false;
        }

        return true;
    }
}