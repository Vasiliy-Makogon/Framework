<?php

class Krugozor_Module_Module_Mapper_Controller extends Krugozor_Mapper_Common
{
    /**
     * Возвращает список контроллеров модуля.
     *
     * @param Krugozor_Module_Module_Model_Module $module
     * @return Krugozor_Cover_Array
     */
    public function findControllersListByModule(Krugozor_Module_Module_Model_Module $module)
    {
        $params = [
            'where' => array('controller_id_module = ?i' => array($module->getId())),
            'order' => array('controller_name' => 'ASC')
        ];

        return parent::findModelListByParams($params);
    }
}