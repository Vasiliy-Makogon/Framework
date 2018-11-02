<?php

namespace Krugozor\Framework\Module\Module\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Module\Module\Model\Module as ModuleModel;

class Controller extends CommonMapper
{
    /**
     * Возвращает список контроллеров модуля.
     *
     * @param ModuleModel $module
     * @return CoverArray
     */
    public function findControllersListByModule(ModuleModel $module): CoverArray
    {
        $params = [
            'where' => array('controller_id_module = ?i' => array($module->getId())),
            'order' => array('controller_name' => 'ASC')
        ];

        return parent::findModelListByParams($params);
    }
}