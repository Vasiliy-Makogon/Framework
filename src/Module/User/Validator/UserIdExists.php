<?php

namespace Krugozor\Framework\Module\User\Validator;

use Krugozor\Framework\Module\User\Mapper\User as UserMapper;
use Krugozor\Framework\Validator\ValidatorAbstract;

class UserIdExists extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'USER_WITH_ID_NOT_EXISTS';

    /**
     * UserIdExists constructor.
     * @param string $value проверяемый ID
     * @param UserMapper $mapper
     */
    public function __construct(string $value, UserMapper $mapper)
    {
        parent::__construct($value);

        $this->mapper = $mapper;
    }

    /**
     * Возвращает false (факт ошибки), если пользователь с указанным ID не найден.
     *
     * @return bool
     */
    public function validate(): bool
    {
        if (!$this->mapper->findModelById($this->value)->getId()) {
            $this->error_params = array('id' => $this->value);

            return false;
        }

        return true;
    }
}