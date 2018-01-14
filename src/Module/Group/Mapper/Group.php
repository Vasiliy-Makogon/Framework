<?php

class Krugozor_Module_Group_Mapper_Group extends Krugozor_Mapper_Common
{
    /**
     * Сохраняет данные группы вместе с правами доступа и выполняет денормализацию
     * прав доступа с записью в поле `group_access` таблицы `group`.
     *
     * @see Krugozor_Mapper_Common::save()
     */
    public function saveModel(Krugozor_Model $object)
    {
        parent::saveModel($object);

        if ($object->getId()) {
            $this->getMapperManager()->getMapper('Group/Access')->saveAccesses($object);
        }

        // Денормализация прав группы.
        $access = $this->getMapperManager()
            ->getMapper('Group/Access')
            ->getGroupAccessByIdWithControllerNames($object->getId())
            ->getDataAsArray();

        $object->setAccess(serialize($access));

        parent::saveModel($object);

        return $object;
    }

    /**
     * Удаляет группу $group, её доступы и связывает пользователей,
     * закрепленных за этой группой, с группой "Пользователи".
     *
     * @param Krugozor_Module_Group_Model_Group $group
     * @return void
     */
    public function delete(Krugozor_Module_Group_Model_Group $group)
    {
        $this->getMapperManager()->getMapper('Group/Access')->clearByGroup($group);
        $this->getMapperManager()->getMapper('User/User')->setDefaultGroupForUsersWithGroup($group);
        parent::deleteById($group);
    }

    /**
     * Находит все группы, за исклюением группы гостей.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function findAllGroupsWithoutGuest()
    {
        return parent::findModelListByParams(array('where' => 'group_alias <> "guest"'));
    }

    /**
     * Ищет группу по алиасу группы.
     *
     * @param string $group_alias алиас группы
     * @return Krugozor_Module_Group_Model_Group
     */
    public function findGroupByAlias($group_alias)
    {
        $params = array('where' => array('group_alias = "?s"' => array($group_alias)));

        return parent::findModelByParams($params);
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