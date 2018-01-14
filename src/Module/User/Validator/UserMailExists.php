<?php

class Krugozor_Module_User_Validator_UserMailExists extends Krugozor_Validator_Abstract
{
    protected $error_key = 'USER_MAIL_EXISTS';

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
            'where' => array('user_email = "?s"' => array($this->value->getEmail()->getValue())),
        );

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