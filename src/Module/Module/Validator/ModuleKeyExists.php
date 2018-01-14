<?php
class Krugozor_Module_Module_Validator_ModuleKeyExists extends Krugozor_Validator_Abstract
{
    protected $error_key = 'MODULE_KEY_EXISTS';

    /**
     * @param Krugozor_Module_Module_Model_Module $value объект модуля
     * @param Krugozor_Mapper $mapper
     */
    public function __construct(Krugozor_Module_Module_Model_Module $value,
                                Krugozor_Mapper $mapper)
    {
        parent::__construct($value);

        $this->mapper = $mapper;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        $params = array (
            'where' => array('module_key = "?s"' => array($this->value->getKey())),
            'what' => 'id',
        );

        if ($this->value->getId() !== null) {
            $params['where']['AND id <> ?i'] = array($this->value->getId());
        }

        if ($this->mapper->findModelByParams($params)->getId()) {
            $this->error_params = array('module_key' => $this->value->getKey());

            return false;
        }

        return true;
    }
}