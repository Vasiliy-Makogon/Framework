<?php
class Krugozor_Module_Advert_Type_AdvertType implements Krugozor_Type_Interface
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
    public function __construct($advert_type)
    {
        $this->advert_type = $advert_type;
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Type_Interface::getValue()
     */
    public function getValue()
    {
        return $this->advert_type;
    }

    /**
     * Возвращает значение типа как человекопонятную строку.
     *
     * @param void
     * @return string
     */
    public function getAsText()
    {
        return isset(self::$advert_types[$this->advert_type])
               ? self::$advert_types[$this->advert_type]
               : null;
    }

    /**
     * Возвращает self::$advert_types
     *
     * @param void
     * @return array
     */
    public static function getTypes()
    {
        return self::$advert_types;
    }

    /**
     * Возвращает self::$advert_types_simple
     *
     * @param void
     * @return array
     */
    public static function getSimpleTypes()
    {
        return self::$advert_types_simple;
    }
}