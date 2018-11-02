<?php

namespace Krugozor\Framework\Module\User\Mapper;

use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Model;

class InviteAnonymousUser extends CommonMapper
{
    /**
     * @param Model $object
     * @return bool|\Krugozor\Database\Mysql\Statement
     */
    public function insert(Model $object)
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
    public function deleteByUniqueCookieId(string $unique_cookie_id)
    {
        $this->getMapperManager()->getMapper('User/InviteAnonymousUser')->deleteByParams([
            'where' => ['unique_cookie_id = "?s"' => [$unique_cookie_id]]
        ]);

        return $this;
    }
}