<?php

namespace Krugozor\Framework\Module\User\Type;

use Krugozor\Framework\Type\TypeInterface;

class Sex implements TypeInterface
{
    /**
     * @var string
     */
    protected $sex;

    /**
     * @var array
     */
    protected static $sex_types = array('M' => 'Мужчина', 'F' => 'Женщина');

    /**
     * @param string $sex
     */
    public function __construct($sex)
    {
        if (isset(self::$sex_types[$sex])) {
            $this->sex = $sex;
        }
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->sex;
    }

    /**
     * @return string
     */
    public function getAsText()
    {
        return self::$sex_types[$this->sex];
    }

    /**
     * @return array
     */
    public static function getTypes()
    {
        return self::$sex_types;
    }
}