<?php

/**
 * Проверка на наличия URL-адреса в значении $this->value.
 * Подразумевается, что $this->value - это некий текст, введенный пользователем.
 */
class Krugozor_Validator_HasBadUrl extends Krugozor_Validator_Abstract
{
    protected $error_key = 'BAD_URL_IN_TEXT';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        preg_match_all(Krugozor_Static_String::$url_pattern_search, $this->value, $matches);

        if (!empty($matches[0])) {
            $this->error_params = array('url' => implode(', ', $matches[0]));

            return false;
        }

        return true;
    }
}