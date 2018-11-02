<?php

namespace Krugozor\Framework\Module\Group\Mapper;

use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Model;
use Krugozor\Framework\Module\Group\Model\Group as GroupModel;

class Group extends CommonMapper
{
    /**
     * Сохраняет данные группы вместе с правами доступа и выполняет денормализацию
     * прав доступа с записью в поле `group_access` таблицы `group`.
     *
     * @param Model $object
     * @return Model
     */
    public function saveModel(Model $object): Model
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
     * @param Group $group
     */
    public function delete(GroupModel $group)
    {
        $this->getMapperManager()->getMapper('Group/Access')->clearByGroup($group);
        $this->getMapperManager()->getMapper('User/User')->setDefaultGroupForUsersWithGroup($group);
        parent::deleteById($group);
    }

    /**
     * Находит все группы, за исклюением группы гостей.
     *
     * @return CoverArray
     */
    public function findAllGroupsWithoutGuest()
    {
        return parent::findModelListByParams(array('where' => 'group_alias <> "guest"'));
    }

    /**
     * Ищет группу по алиасу группы.
     *
     * @param string $group_alias алиас группы
     * @return GroupModel
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
     * @return CoverArray
     */
    public function findListForBackend(array $params = array())
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }
}