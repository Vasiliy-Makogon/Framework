<?php

class Krugozor_Module_User_Validator_UserLoginExists extends Krugozor_Validator_Abstract
{
    protected $error_key = 'USER_LOGIN_EXISTS';

    /**
     * @param Krugozor_Module_User_Model_User $value объект пользователя
     * @param Krugozor_Mapper $mapper
     */
    public function __construct($value, Krugozor_Mapper $mapper)
    {
        parent::__construct($value);

        $this->mapper = $mapper;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        $params = array
        (
            'where' => array('user_login = "?s"' => array($this->value->getLogin())),
        );

        if ($this->value->getId() !== null) {
            $params['where']['AND id <> ?i'] = array($this->value->getId());
        }

        if ($this->mapper->findModelByParams($params)->getId()) {
            $this->error_params = array('user_login' => $this->value->getLogin());

            return false;
        }

        return true;
    }
}