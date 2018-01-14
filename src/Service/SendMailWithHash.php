<?php

/**
 * Базовый сервис для отправки писем с хэшем с целью проверки подлинности пользовательских операций.
 */
abstract class Krugozor_Service_SendMailWithHash
{
    /**
     * @var Krugozor_Module_User_Mapper_User
     */
    protected $user_mapper;

    /**
     * @var Krugozor_Module_User_Model_User
     */
    protected $user;

    /**
     * @var Krugozor_Mail
     */
    protected $mail;

    /**
     * @param Krugozor_Module_User_Mapper_User $user_mapper
     */
    public function __construct(Krugozor_Module_User_Mapper_User $user_mapper)
    {
        $this->user_mapper = $user_mapper;
    }

    /**
     * @param Krugozor_Module_User_Model_User $user
     * @return Krugozor_Service_SendMailWithHash
     */
    public function setUser(Krugozor_Module_User_Model_User $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Krugozor_Mail $mail
     * @return Krugozor_Service_SendMailWithHash
     */
    public function setMail(Krugozor_Mail $mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Отправляет письмо с уникальной ссылкой.
     *
     * @param void
     * @return boolean
     */
    abstract public function sendEmailWithHash();

    /**
     * Проверяет хэш $hash на валидность.
     * В случае успеха инстанцирует объект пользователя
     * и очищает таблицу учёта хэшей от записи с данными.
     *
     * @param string $hash хэш
     * @return boolean
     */
    abstract public function isValidHash($hash);

    /**
     * Проверяет, инстанцирован ли объект почты.
     *
     * @param void
     * @throws InvalidArgumentException
     */
    protected function checkMailObjectInstance()
    {
        if ($this->mail === null) {
            throw new InvalidArgumentException(__METHOD__ . ': Не передан объект почты');
        }
    }

    /**
     * Проверяет, инстанцирован ли объект пользователя.
     *
     * @param void
     * @throws InvalidArgumentException
     */
    protected function checkUserObjectInstance()
    {
        if (!is_object($this->user) || !$this->user instanceof Krugozor_Module_User_Model_User) {
            throw new InvalidArgumentException(__METHOD__ . ': Не инстанцирован объект пользователя');
        }
    }
}