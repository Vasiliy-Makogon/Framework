<?php
class Krugozor_Module_Advert_Type_PriceType implements Krugozor_Type_Interface
{
    protected $price_type;

    protected static $price_types = array
    (
        'RUB' => array('рубли', '&#8381;'),
        'USD' => array('доллары США', '$'),
        'EUR' => array('евро', '€')
    );

    public function __construct($price_type)
    {
        $this->price_type = strtoupper($price_type);
    }

    public function getValue()
    {
        return $this->price_type;
    }

    public function getAsSymbol()
    {
        return isset(self::$price_types[$this->price_type])
               ? self::$price_types[$this->price_type][1]
               : null;
    }

    public static function getTypes()
    {
        return self::$price_types;
    }
}