<?php

namespace Krugozor\Framework\Service;

use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Mail;
use Krugozor\Framework\Module\User\Mapper\User as UserMapper;

/**
 * Базовый сервис для отправки писем с хэшем с целью проверки
 * подлинности пользовательских операций.
 */
abstract class SendMailWithHash
{
    /**
     * @var UserMapper
     */
    protected $user_mapper;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Mail
     */
    protected $mail;

    /**
     * @param UserMapper $user_mapper
     */
    public function __construct(UserMapper $user_mapper)
    {
        $this->user_mapper = $user_mapper;
    }

    /**
     * @param User $user
     * @return SendMailWithHash
     */
    public function setUser(User $user): self
    {
        $this->user = $user;

        return $this;
    }

    /**
     * @param Mail $mail
     * @return SendMailWithHash
     */
    public function setMail(Mail $mail): self
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Отправляет письмо с уникальной ссылкой.
     *
     * @return bool
     */
    abstract public function sendEmailWithHash(): bool;

    /**
     * Проверяет хэш $hash на валидность.
     * В случае успеха инстанцирует объект пользователя
     * и очищает таблицу учёта хэшей от записи с данными.
     *
     * @param string $hash хэш
     * @return bool
     */
    abstract public function isValidHash(string $hash): bool;

    /**
     * Проверяет, инстанцирован ли объект почты.
     *
     * @throws InvalidArgumentException
     */
    protected function checkMailObjectInstance()
    {
        if ($this->mail === null) {
            throw new \InvalidArgumentException(
                __METHOD__ . ': Не передан объект почты'
            );
        }
    }

    /**
     * Проверяет, инстанцирован ли объект пользователя.
     *
     * @throws \InvalidArgumentException
     */
    protected function checkUserObjectInstance()
    {
        if (!is_object($this->user) || !$this->user instanceof User) {
            throw new \InvalidArgumentException(
                __METHOD__ . ': Не инстанцирован объект пользователя'
            );
        }
    }
}