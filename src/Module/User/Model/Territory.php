<?php

/**
 * Базовый класс для регионов, т.е. для стран, областей и городов.
 * Свойство $url устанавливается в результате работы класса Krugozor_Module_User_Cover_TerritoryList
 */
abstract class Krugozor_Module_User_Model_Territory extends Krugozor_Model
{
    /**
     * URL-адрес региона с учетом регионов-родителей, например:
     * /russia - для стран
     * /russia/moskovskaja - для регионов
     * /russia/moskovskaja/moskva - для городов
     *
     * @var string
     */
    protected $url;

    /**
     * return string
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * @param string $url
     * @return Krugozor_Module_User_Model_Territory
     */
    public function setUrl($url)
    {
        $this->url = $url;

        return $this;
    }
}
