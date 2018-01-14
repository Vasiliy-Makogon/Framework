<?php

class Krugozor_Type_Datetime extends DateTime
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

    public function __construct($time = 'now', DateTimeZone $timezone = null)
    {
        if (empty($time) || false === strtotime($time)) {
            throw new UnexpectedValueException();
        }

        parent::__construct($time, self::getBugTimezone($timezone));
    }

    /**
     * При наследовании от него все методы, которые возвращают новый инстанс DateTime,
     * возвращают именно DateTime, а не то, что хочется.
     *
     * @see parent::createFromFormat()
     */
    public static function createFromFormat($format, $time, $timezone = null)
    {
        $datetime = parent::createFromFormat($format, $time, self::getBugTimezone($timezone));

        $this_instance = new self();

        return $this_instance->setTimestamp($datetime->getTimestamp());
    }

    /**
     * Возвращает строку времени формата self::FORMAT_DATETIME
     *
     * @param void
     * @return string
     */
    public function formatAsMysqlDatetime()
    {
        return $this->format(self::FORMAT_DATETIME);
    }

    /**
     * Формирует дату для HTTP заголовка LastModified
     *
     * @param void
     * @return string
     */
    public function formatHttpDate()
    {
        return gmdate(self::FORMAT_RFC1123, $this->getTimestamp());
    }

    /**
     * Функция возвращает строковое человекопонятное представление времени.
     *
     * @param void
     * @return string
     */
    public function formatDateForPeople()
    {
        // Вчерашняя дата с 0 часов
        $yesterday_begin = mktime(0, 0, 0, date("m"), date("d") - 1, date("Y"));

        // Вчерашняя дата, за секунду до следующего дня.
        $yesterday_end = mktime(23, 59, 59, date("m"), date("d") - 1, date("Y"));

        if ($this->getTimestamp() >= $yesterday_begin && $this->getTimestamp() <= $yesterday_end) {
            return 'Вчера в ' . $this->format('H:i');
        } else if ($this->getTimestamp() <= $yesterday_end) {
            return $this->format('d.m.Y H:i');
        } else {
            return 'Сегодня в ' . $this->format('H:i');
        }
    }

    /**
     * Bug #52063   DateTime constructor's second argument doesn't have a null default value
     * http://bugs.php.net/bug.php?id=52063&thanks=6
     *
     * @todo: Исправлено в 5.3.6. Надо проверить.
     */
    private static function getBugTimezone($timezone)
    {
        return null === $timezone || !$timezone instanceof DateTimeZone
            ? new DateTimeZone(self::DEFAULT_TIMEZONE)
            : $timezone;
    }
}