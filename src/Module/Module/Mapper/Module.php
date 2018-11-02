<?php

namespace Krugozor\Framework\Module\Module\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Module\Module\Model\Module as ModuleModel;

class Module extends CommonMapper
{
    /**
     * Удаляет модуль и подчиненные контроллеры.
     *
     * @param ModuleModel $module
     */
    public function delete(ModuleModel $module)
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
     * @return CoverArray
     */
    public function findListForBackend(array $params = array()): CoverArray
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }
}