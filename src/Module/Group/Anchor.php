<?php

namespace Krugozor\Framework\Module\Group;

class Anchor extends \Krugozor\Framework\Anchor
{
    public static function getPath(string $path = null)
    {
        return dirname(__FILE__) . self::addPath($path);
    }
}