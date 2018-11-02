<?php

namespace Krugozor\Framework\Module\Advert\Type;

use Krugozor\Framework\Type\TypeInterface;

class AdvertType implements TypeInterface
{
    protected $advert_type;

    /**
     * Типы возможных предложений.
     *
     * @var array
     */
    protected static $advert_types = array
    (
        'sale' => 'Предложение',
        'buy' => 'Спрос',
    );

    /**
     * Типы возможных предложений для пользователей, которые не понимают в чем отличие
     * "спроса" от "предложения", т.е. для идиотов.
     *
     * @var array
     */
    protected static $advert_types_simple = array
    (
        'sale' => 'Предлагаю',
        'buy' => 'Ищу'
    );

    /**
     * @param string $advert_type тип предложения
     */
    public function __construct(?string $advert_type)
    {
        $this->advert_type = $advert_type;
    }

    /**
     * @return mixed|string
     */
    public function getValue()
    {
        return $this->advert_type;
    }

    /**
     * Возвращает значение типа как человекопонятную строку.
     *
     * @return string
     */
    public function getAsText(): string
    {
        return isset(self::$advert_types[$this->advert_type])
            ? self::$advert_types[$this->advert_type]
            : null;
    }

    /**
     * Возвращает self::$advert_types
     *
     * @return array
     */
    public static function getTypes(): array
    {
        return self::$advert_types;
    }

    /**
     * Возвращает self::$advert_types_simple
     *
     * @return array
     */
    public static function getSimpleTypes(): array
    {
        return self::$advert_types_simple;
    }
}