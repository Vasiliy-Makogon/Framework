<?php

class Krugozor_Module_User_Cover_TerritoryList extends Krugozor_Cover_Array
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Cover_Abstract_Array::__set()
     */
    public function __set($key, $value)
    {
        $url = ($this->getLast() ? $this->getLast()->getUrl() : '') . '/' . $value->getNameEn();

        $value->setUrl($url);

        parent::__set($key, $value);
    }
}