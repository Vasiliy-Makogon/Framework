<?php

/**
 * Проверка на наличия email-адреса в значении $this->value.
 * Подразумевается, что $this->value - это некий текст, введенный пользователем.
 */
class Krugozor_Validator_HasBadEmail extends Krugozor_Validator_Abstract
{
    protected $error_key = 'BAD_EMAIL_IN_TEXT';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        preg_match_all(Krugozor_Static_String::$email_pattern_search, $this->value, $matches);

        if (!empty($matches[0])) {
            $this->error_params = array('email' => implode(', ', $matches[0]));

            return false;
        }

        return true;
    }
}