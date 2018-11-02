<?php

namespace Krugozor\Framework\Module\User\Validator;

use Krugozor\Framework\Module\User\Mapper\User as UserMapper;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Validator\ValidatorAbstract;

class UserMailExists extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'USER_MAIL_EXISTS';

    /**
     * UserMailExists constructor.
     * @param User $value
     * @param UserMapper $mapper
     */
    public function __construct(User $value, UserMapper $mapper)
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
                'user_email = "?s"' => [$this->value->getEmail()->getValue()]
            ],
        ];

        if ($this->value->getId() !== null) {
            $params['where']['AND id <> ?i'] = array($this->value->getId());
        }

        if ($this->mapper->findModelByParams($params)->getId()) {
            $this->error_params = array('user_email' => $this->value->getEmail()->getValue());
            return false;
        }

        return true;
    }
}