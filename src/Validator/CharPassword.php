<?php

/**
 * Проверяет значение на присутствие недопустимых в пароле символов.
 */
class Krugozor_Validator_CharPassword extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_STRING_CHAR_PASS';

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        return self::isCorrectCharsPass($this->value);
    }

    /**
     * Ищет в строке символы отличные от 'a-z', '0-9', '_', '-'.
     * Возвращает true в случае если ввод корректен, false в противном случае.
     *
     * @param string проверяемая строка
     * @return boolean
     */
    public static function isCorrectCharsPass($in)
    {
        return !preg_match("~[^a-z0-9_-]+~i", $in);
    }
}