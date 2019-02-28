<?php

namespace Krugozor\Framework;

abstract class Anchor
{
    /**
     * @param string|null $path
     * @return string
     */
    protected static function addPath(string $path = null)
    {
        return $path !== null ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '';
    }
}