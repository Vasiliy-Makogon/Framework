<?php

namespace Krugozor\Framework\Module\User\Model;

use Krugozor\Framework\Model;

/**
 * Базовый класс для регионов, т.е. для стран, областей и городов.
 * Свойство $url устанавливается в результате работы класса Krugozor\Framework\Module\User\Cover\TerritoryList
 */
abstract class Territory extends Model
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
     * @return Territory
     */
    public function setUrl(string $url)
    {
        $this->url = $url;

        return $this;
    }
}
