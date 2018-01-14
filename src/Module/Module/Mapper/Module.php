<?php

class Krugozor_Module_Module_Mapper_Module extends Krugozor_Mapper_Common
{
    /**
     * Удаляет модуль и подчиненные контроллеры.
     *
     * @param Krugozor_Module_Module_Model_Module $module
     * @return void
     */
    public function delete(Krugozor_Module_Module_Model_Module $module)
    {
        parent::deleteById($module);

        $params = array(
            'where' => array('`controller_id_module` = ?i' => array($module->getId()))
        );

        $this->getMapperManager()->getMapper('Module/Controller')->deleteByParams($params);
    }

    /**
     * Возвращает список записей для админитративной части.
     *
     * @param array $params
     * @return Krugozor_Cover_Array
     */
    public function findListForBackend(array $params = array())
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }
}