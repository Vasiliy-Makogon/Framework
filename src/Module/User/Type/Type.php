<?php

class Krugozor_Module_User_Type_Type implements Krugozor_Type_Interface
{
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
     * (non-PHPdoc)
     * @see Krugozor_Type_Interface::getValue()
     */
    public function getValue()
    {
        return $this->user_type;
    }

    /**
     * Возвращает значение типа как человекопонятную строку.
     *
     * @param void
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
     * @param void
     * @return array
     */
    public static function getTypes()
    {
        return self::$user_types;
    }
}