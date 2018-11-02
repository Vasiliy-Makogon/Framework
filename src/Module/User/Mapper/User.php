<?php

namespace Krugozor\Framework\Module\User\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Module\User\Model\User as UserModel;
use Krugozor\Framework\Module\Group\Model\Group as GroupModel;

class User extends CommonMapper
{
    /**
     * Возвращает массив данных пользователей для вывода
     * в списке админ-интерфейса.
     *
     * @param array
     * @return CoverArray
     */
    public function getUsersListWithResidence(array $params = [])
    {
        $params['group'] = array('user.id' => 'DESC');

        $params = self::makeSqlFromParams($params);

        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    COUNT(advert.id) as `advert_count`,
                    `user`.`id`,
                    `user`.`user_ip`,
                    `user`.user_active,
                    `user`.user_group,
                    `user`.user_first_name,
                    `user`.user_last_name,
                    `user`.user_login,
                    `user`.user_last_name,
                    `user`.user_regdate,
                    `user`.user_visitdate,
                    `user`.user_email,
                    `user`.user_url,
                    `user`.user_icq,
                    `user`.user_city,
                    `user`.user_region,
                    `user`.user_country,
                    `user-country`.id,
                    `user-country`.`country_name_ru`,
                    `user-region`.`id`,
                    `user-region`.`region_name_ru`,
                    `user-city`.`id`,
                    `user-city`.`city_name_ru`
                FROM `user`
                LEFT JOIN `user-country` ON `user`.`user_country` = `user-country`.`id`
                LEFT JOIN `user-region` ON `user`.`user_region` = `user-region`.`id`
                LEFT JOIN `user-city` ON `user`.`user_city` = `user-city`.`id`
                LEFT JOIN `advert` ON `user`.`id` = `advert`.`advert_id_user`
            ' . $params['where'] . $params['group'] . $params['order'] . $params['limit'];

        array_unshift($params['args'], $sql);

        return parent::result2objects(call_user_func_array(array($this->getDb(), 'query'), $params['args']));
    }

    /**
     * Возвращает доменный объект UserModel
     * находя его по $id, хешу md5 и соли $salt.
     * Используется при авторизации через куки (автологин).
     *
     * @param int $id
     * @param string $md5password
     * @param string $salt
     * @return UserModel
     */
    public function findByLoginHash($id, $md5password, $salt)
    {
        $sql = 'SELECT * FROM ?f WHERE id = ?i AND MD5(CONCAT(`user_login`, `user_password`, "?s")) = "?s"';

        $res = $this->getDb()->query($sql, $this->getTableName(), $id, $salt, $md5password);

        if (is_object($res) && $res->getNumRows() > 0) {
            return parent::createModelFromDatabaseResult($res->fetch_assoc());
        }

        return false;
    }

    /**
     * Возвращает доменный объект находя его по логину.
     *
     * @param string
     * @return UserModel
     */
    public function findByLogin($login)
    {
        $params = array
        (
            'where' => array('`user_login` = "?s"' => array($login))
        );

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает доменный объект находя его по email.
     *
     * @param string
     * @return UserModel
     */
    public function findByEmail($mail)
    {
        $params = [
            'where' => ['`user_email` = "?s"' => [$mail]]
        ];

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает доменный объект находя его по $login и $password.
     * Используется при авторизации из POST.
     *
     * @param string $login логин из POST-запроса
     * @param string $password пароль из POST-запроса
     * @return UserModel
     */
    public function findByLoginPassword($login, $password)
    {
        $params = array
        (
            'where' => array('`user_login` = "?s" AND MD5("?s") = `user_password`' => array($login, $password))
        );

        return parent::findModelByParams($params);
    }

    /**
     * Устанавливает для пользователей группы с
     * идентификатором ID группу по умолчанию (user).
     *
     * @param GroupModel
     * @return bool
     */
    public function setDefaultGroupForUsersWithGroup(GroupModel $group)
    {
        $sql = '
            UPDATE ?f SET
                `user_group` = (SELECT `id` FROM `group` WHERE `group_alias` = "user")
            WHERE
                `user_group` = ?i';

        return $this->getDb()->query($sql, $this->getTableName(), $group->getId());
    }

    /**
     * Находит последних активных пользователей за 5 минут.
     *
     * @return CoverArray
     */
    public function findUsersOnline()
    {
        $params['what'] = 'user_first_name, user_last_name, user_login, user_visitdate';
        $params['where'] = array('`user_visitdate` > NOW() - INTERVAL 10 MINUTE' => array());

        return parent::findModelListByParams($params);
    }
}