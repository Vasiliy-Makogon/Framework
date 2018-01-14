<?php
class Krugozor_Module_Module_Validator_ModuleNameExists extends Krugozor_Validator_Abstract
{
    protected $error_key = 'MODULE_NAME_EXISTS';

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
        $params = array(
            'where' => array('module_name = "?s"' => array($this->value->getName())),
            'what' => 'id',
        );

        if ($this->value->getId() !== null) {
            $params['where']['AND id <> ?i'] = array($this->value->getId());
        }

        if ($this->mapper->findModelByParams($params)->getId()) {
            $this->error_params = array('module_name' => $this->value->getName());

            return false;
        }

        return true;
    }
}