<?php

namespace Krugozor\Framework\Module\User\Validator;

use Krugozor\Framework\Mapper;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Validator\ValidatorAbstract;

class UserLoginExists extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'USER_LOGIN_EXISTS';

    /**
     * UserLoginExists constructor.
     * @param User $value
     * @param Mapper $mapper
     */
    public function __construct(User $value, Mapper $mapper)
    {
        parent::__construct($value);

        $this->mapper = $mapper;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        $params = [
            'where' => [
                'user_login = "?s"' => [$this->value->getLogin()]
            ],
        ];

        if ($this->value->getId() !== null) {
            $params['where']['AND id <> ?i'] = [$this->value->getId()];
        }

        if ($this->mapper->findModelByParams($params)->getId()) {
            $this->error_params = ['user_login' => $this->value->getLogin()];
            return false;
        }

        return true;
    }
}