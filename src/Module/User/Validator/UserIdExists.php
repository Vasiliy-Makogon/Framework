<?php

class Krugozor_Module_User_Validator_UserIdExists extends Krugozor_Validator_Abstract
{
    protected $error_key = 'USER_WITH_ID_NOT_EXISTS';

    /**
     * @param int $value ID пользователя
     * @param Krugozor_Mapper $mapper
     */
    public function __construct($value, Krugozor_Mapper $mapper)
    {
        parent::__construct($value);

        $this->mapper = $mapper;
    }

    /**
     * Возвращает false (факт ошибки), если пользователь с указанным ID не найден.
     *
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (!$this->mapper->findModelById($this->value)->getId()) {
            $this->error_params = array('id' => $this->value);

            return false;
        }

        return true;
    }
}