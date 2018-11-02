<?php

namespace Krugozor\Framework\Module\Advert\Type;

use Krugozor\Framework\Type\TypeInterface;

class PriceType implements TypeInterface
{
    /**
     * @var string
     */
    protected $price_type;

    /**
     * @var array
     */
    protected static $price_types = [
        'RUB' => array('рубли', '&#8381;'),
        'USD' => array('доллары США', '$'),
        'EUR' => array('евро', '€')
    ];

    /**
     * @param string $price_type
     */
    public function __construct(string $price_type)
    {
        $this->price_type = strtoupper($price_type);
    }

    /**
     * @return mixed|string
     */
    public function getValue()
    {
        return $this->price_type;
    }

    /**
     * @return null|string
     */
    public function getAsSymbol(): ?string
    {
        return
            isset(self::$price_types[$this->price_type])
            ? self::$price_types[$this->price_type][1]
            : null;
    }

    /**
     * @return array
     */
    public static function getTypes(): array
    {
        return self::$price_types;
    }
}