<?php

namespace Krugozor\Framework\Module\Getpassword\Mapper;

use Krugozor\Framework\Mapper\CommonMapper;

class Getpassword extends CommonMapper
{
    /**
     * Находит объект по хэшу.
     *
     * @param string $hash
     * @return Model
     */
    public function findByHash($hash)
    {
        $params = array(
            'where' => [
                'hash = "?s"' => [$hash],
            ],
        );

        return parent::findModelByParams($params);
    }
}