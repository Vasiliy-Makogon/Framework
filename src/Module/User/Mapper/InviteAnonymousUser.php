<?php

class Krugozor_Module_User_Mapper_InviteAnonymousUser extends Krugozor_Mapper_Common
{
    /**
     * @param Krugozor_Model $object
     * @return \Krugozor\Database\Mysql\Statement
     */
    public function insert(Krugozor_Model $object)
    {
        $sql = 'INSERT INTO ?f
                SET `unique_cookie_id` = "?s", `send_date` = now()
                ON DUPLICATE KEY UPDATE `send_date` = now()';

        return $this->getDb()->query($sql, $this->getTableName(), $object->getUniqueCookieId());
    }

    /**
     * @param string $unique_cookie_id
     * @return $this
     */
    public function deleteByUniqueCookieId($unique_cookie_id)
    {
        $this->getMapperManager()->getMapper('User/InviteAnonymousUser')->deleteByParams([
            'where' => ['unique_cookie_id = "?s"' => [$unique_cookie_id]]
        ]);

        return $this;
    }
}