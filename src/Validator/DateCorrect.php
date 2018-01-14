<?php

/**
 * Возвращает true, если значение - объект типа Krugozor_Type_Datetime, строка 'now',
 * пустая строка или строка в формате $this->format.
 */
class Krugozor_Validator_DateCorrect extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_DATETIME';

    /**
     * Формат проверяемой даты.
     *
     * @var string
     */
    private $format;

    /**
     * Устанавливает формат проверяемой даты.
     *
     * @param string
     * @return Krugozor_Validator_DateCorrect
     */
    public function setFormat($format)
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Возвращает true, если дата в виде строки $value
     * соответствует шаблону $this->format, false в обратном случае.
     *
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (!$this->value || is_string($this->value) && strtolower($this->value) == 'now') {
            return true;
        }

        if (is_object($this->value)) {
            if ($this->value instanceof Krugozor_Type_Datetime) {
                return true;
            }

            throw new RuntimeException('Проверяемая дата не является объектом типа Krugozor_Type_Datetime');
        }

        if (!$this->format) {
            throw new RuntimeException('Не указан формат проверяемой даты');
        }

        $date = DateTime::createFromFormat($this->format, $this->value);
        return $date && $date->format($this->format) == $this->value;
    }
}