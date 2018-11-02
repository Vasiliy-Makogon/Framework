<?php

namespace Krugozor\Framework\Module\User\Validator;

use Krugozor\Framework\Validator\ValidatorAbstract;

class UserPasswordsCompare extends ValidatorAbstract
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
     * @return bool
     */
    public function validate() :bool
    {
        return $this->password_1 === $this->password_2;
    }
}