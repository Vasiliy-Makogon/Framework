<?php

class Krugozor_Module_User_Validator_UserPasswordsCompare extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INCORRECT_PASSWORDS';

    /**
     * Проверяемый пароль № 1
     *
     * @var string
     */
    private $password_1;

    /**
     * Проверяемый пароль № 2
     *
     * @var string
     */
    private $password_2;

    /**
     * @param string $password_1 Строка пароля №1
     * @param string $password_2 Строка пароля №2
     */
    public function __construct($password_1, $password_2)
    {
        parent::__construct(null);

        $this->password_1 = (string)$password_1;
        $this->password_2 = (string)$password_2;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        return $this->password_1 === $this->password_2;
    }
}