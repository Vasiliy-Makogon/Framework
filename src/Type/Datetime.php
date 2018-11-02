<?php

namespace Krugozor\Framework\Type;

class Datetime extends \DateTime
{
    /**
     * Временная зона по умолчанию.
     *
     * @var string
     */
    const DEFAULT_TIMEZONE = 'Europe/Moscow';

    /**
     * Формат даты вида 'YYYY-MM-DD HH:MM:SS'
     *
     * @var string
     */
    const FORMAT_DATETIME = 'Y-m-d H:i:s';

    /**
     * Формат даты для HTTP (RFC 1123) согласно RFC 2068
     *
     * @var string
     */
    const FORMAT_RFC1123 = "D, d M Y H:i:s \G\M\T";

    /**
     * Datetime constructor.
     * @param string $time
     * @param \DateTimeZone|null $timezone
     */
    public function __construct($time = 'now', \DateTimeZone $timezone = null)
    {
        if (empty($time) || false === strtotime($time)) {
            throw new \UnexpectedValueException();
        }

        parent::__construct($time, $timezone);
    }

    /**
     * При наследовании от него все методы, которые возвращают новый инстанс DateTime,
     * возвращают именно DateTime, а не объект данного типа.
     *
     * @param string $format
     * @param string $time
     * @param null $timezone
     * @return Datetime
     */
    public static function createFromFormat($format, $time, $timezone = null): self
    {
        $datetime = parent::createFromFormat($format, $time, $timezone);
        $this_instance = new self();
        return $this_instance->setTimestamp($datetime->getTimestamp());
    }

    /**
     * Возвращает строку времени формата self::FORMAT_DATETIME
     *
     * @return string
     */
    public function formatAsMysqlDatetime(): string
    {
        return $this->format(self::FORMAT_DATETIME);
    }

    /**
     * Формирует дату для HTTP заголовка LastModified
     *
     * @return string
     */
    public function formatHttpDate(): string
    {
        return gmdate(self::FORMAT_RFC1123, $this->getTimestamp());
    }

    /**
     * Функция возвращает строковое человекопонятное представление времени.
     *
     * @return string
     */
    public function formatDateForPeople(): string
    {
        $yesterday_begin = (new \DateTime('yesterday 00:00:00'))->getTimestamp();
        $yesterday_end = (new \DateTime('yesterday 23:59:59'))->getTimestamp();

        if ($this->getTimestamp() >= $yesterday_begin && $this->getTimestamp() <= $yesterday_end) {
            return 'Вчера в ' . $this->format('H:i');
        } else if ($this->getTimestamp() <= $yesterday_end) {
            return $this->format('d.m.Y H:i');
        } else {
            return 'Сегодня в ' . $this->format('H:i');
        }
    }
}