<?php

namespace Krugozor\Framework\Module\User\Cover;

use Krugozor\Cover\CoverArray;

class TerritoryList extends CoverArray
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        $url = ($this->getLast() ? $this->getLast()->getUrl() : '') . '/' . $value->getNameEn();

        $value->setUrl($url);

        parent::__set($key, $value);
    }
}