<?php

namespace Krugozor\Framework\Service;

use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Module\User\Mapper\User as UserMapper;
use Krugozor\Framework\Module\MailQueue\Model\MailQueue;
use Krugozor\Framework\Module\MailQueue\Mapper\MailQueue as MailQueueMapper;

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
     * @var MailQueueMapper
     */
    protected $mailQueueMapper;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var MailQueue
     */
    protected $mailQueue;

    /**
     * @param UserMapper $user_mapper
     */
    public function __construct()
    {

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
     * @param UserMapper $user_mapper
     * @return SendMailWithHash
     */
    public function setUserMapper(UserMapper $user_mapper): self
    {
        $this->user_mapper = $user_mapper;

        return $this;
    }

    /**
     * @param MailQueue $mailQueue
     * @return SendMailWithHash
     */
    public function setMailQueue(MailQueue $mailQueue): self
    {
        $this->mailQueue = $mailQueue;

        return $this;
    }

    /**
     * @param MailQueueMapper $mailQueueMapper
     * @return SendMailWithHash
     */
    public function setMailQueueMapper(MailQueueMapper $mailQueueMapper): self
    {
        $this->mailQueueMapper = $mailQueueMapper;

        return $this;
    }

    /**
     * Отправляет письмо с уникальной ссылкой.
     */
    abstract public function sendEmailWithHash();

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
     * Проверяет, инстанцирован ли объект почтовой очереди.
     *
     * @throws InvalidArgumentException
     */
    protected function checkMailObjectInstance()
    {
        if ($this->mailQueue === null) {
            throw new \InvalidArgumentException(
                __METHOD__ . ': Не передан объект почтовой очереди'
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