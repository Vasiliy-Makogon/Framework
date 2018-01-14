<?php

class Krugozor_Module_Group_Mapper_Access extends Krugozor_Mapper_Common
{
    /**
     * Возвращает коллекцию объектов доступа группы $group.
     *
     * @param Krugozor_Module_Group_Model_Group $group
     * @return Krugozor_Cover_Array
     */
    public function findByGroup(Krugozor_Module_Group_Model_Group $group)
    {
        $params = array(
            'where' => array('id_group = ?i' => array($group->getId()))
        );

        return parent::findModelListByParams($params);
    }

    /**
     * Удаляет все доступы группы $group.
     *
     * @param Krugozor_Module_Group_Model_Group $group
     * @return bool
     */
    public function clearByGroup(Krugozor_Module_Group_Model_Group $group)
    {
        return $this->getDb()->query('DELETE FROM ?f WHERE `id_group` = ?i', $this->getTableName(), $group->getId());
    }

    /**
     * Сохраняет доступы группы $group.
     *
     * @param Krugozor_Model $group
     * @return bool
     */
    public function saveAccesses(Krugozor_Model $group)
    {
        if (!$group->getAccesses()->count()) {
            return false;
        }

        $this->clearByGroup($group);

        $sql = 'REPLACE INTO ?f (id_group, id_controller, access) VALUES ';
        $args = [];
        $args[] = $this->getTableName();

        foreach ($group->getAccesses() as $access) {
            $sql .= '(?a[?i, ?i, ?i]),';
            $args[] = [$group->getId(), $access->getIdController(), $access->getAccess()];
        }

        $sql = rtrim($sql, ', ');

        return $this->getDb()->queryArguments($sql, $args);
    }

    /**
     * Возвращает объект Krugozor_Cover_Array, где индекс первого уровня вложенности - ключ модуля
     * а значение - объект Krugozor_Cover_Array, ключ которого - ключ контроллера,
     * а значение - значение доступа группы $id_group к данному контроллеру - 1 или 0.
     *
     * Пример:
     * Array
     * (
     *     [User] => Array
     *         (
     *             [BackendMain] => 1
     *             [BackendEdit] => 1
     *             [BackendDelete] => 1
     *             [FrontendEdit] => 1
     *     ...
     * )
     *
     * @param int $id_group
     * @return Krugozor_Cover_Array
     */
    public function getGroupAccessByIdWithControllerNames($id_group)
    {
        $sql = '
             SELECT ?f.`access`, `module`.`module_key`, `module-controller`.`controller_key`
             FROM `module`
             INNER JOIN `module-controller` ON `module`.`id` = `module-controller`.`controller_id_module`
             INNER JOIN ?f ON ?f.`id_controller` = `module-controller`.`id`
             INNER JOIN `group` ON `group`.`id` = ?f.`id_group`
             WHERE `group`.`id` = ?i';

        $res = $this->getDb()->query(
            $sql,
            $this->getTableName(),
            $this->getTableName(),
            $this->getTableName(),
            $this->getTableName(),
            $id_group
        );

        $accesses = new Krugozor_Cover_Array();

        while ($data = $res->fetch_assoc()) {
            if (!isset($accesses[$data['module_key']])) {
                $accesses[$data['module_key']] = array();
            }

            $accesses[$data['module_key']][$data['controller_key']] = $data['access'];
        }

        return $accesses;
    }
}