<?php

namespace Krugozor\Framework\Module\Module\Validator;

use Krugozor\Framework\Module\Module\Model\Module;
use Krugozor\Framework\Validator\ValidatorAbstract;
use Krugozor\Framework\Module\Module\Mapper\Module as ModuleMapper;

class ModuleKeyExists extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'MODULE_KEY_EXISTS';

    /**
     * ModuleKeyExists constructor.
     * @param Module $value
     * @param ModuleMapper $mapper
     */
    public function __construct(Module $value, ModuleMapper $mapper)
    {
        parent::__construct($value);

        $this->mapper = $mapper;
    }

    /**
     * @return bool
     */
    public function validate(): bool
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