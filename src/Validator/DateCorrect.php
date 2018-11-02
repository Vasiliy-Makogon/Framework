<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;
use Krugozor\Framework\Type\Datetime;

/**
 * Возвращает true, если значение - объект типа Krugozor\Framework\Type\Datetime, строка 'now',
 * пустая строка или строка в формате $this->format.
 */
class DateCorrect extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'INVALID_DATETIME';

    /**
     * Формат проверяемой даты.
     * @var string
     */
    private $format;

    /**
     * Устанавливает формат проверяемой даты.
     *
     * @param string $format
     * @return DateCorrect
     */
    public function setFormat(string $format): self
    {
        $this->format = $format;

        return $this;
    }

    /**
     * Возвращает true, если дата в виде строки $value
     * соответствует шаблону $this->format, false в обратном случае.
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value)) {
            return true;
        }

        if (is_string($this->value) && strtolower($this->value) === 'now') {
            return true;
        }

        if (is_object($this->value)) {
            if ($this->value instanceof Datetime) {
                return true;
            }

            throw new \RuntimeException(
                'Проверяемая дата не является объектом типа Krugozor\Framework\Type\Datetime'
            );
        }

        if (!$this->format) {
            throw new \InvalidArgumentException('Не указан формат проверяемой даты');
        }

        $date = \DateTime::createFromFormat($this->format, $this->value);
        return $date && $date->format($this->format) == $this->value;
    }
}