<?php

namespace Krugozor\Framework\Module\User\Type;

use Krugozor\Framework\Type\TypeInterface;

class Type implements TypeInterface
{
    /**
     * @var string
     */
    protected $user_type;

    /**
     * @var array
     */
    protected static $user_types = array
    (
        'private_person' => 'Частное лицо',
        'company' => 'Компания',
    );

    /**
     * @param int $user_type
     */
    public function __construct($user_type)
    {
        $this->user_type = $user_type;
    }

    /**
     * @return int
     */
    public function getValue()
    {
        return $this->user_type;
    }

    /**
     * Возвращает значение типа как человекопонятную строку.
     *
     * @return string
     */
    public function getAsText()
    {
        return isset(self::$user_types[$this->user_type])
            ? self::$user_types[$this->user_type]
            : null;
    }

    /**
     * Возвращает self::$user_types
     *
     * @return array
     */
    public static function getTypes()
    {
        return self::$user_types;
    }
}