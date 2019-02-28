<?php

namespace Krugozor\Framework;

abstract class Anchor
{
    /**
     * Дополняет физический путь к модулю строкой $path.
     * @param string|null $path
     * @return string
     */
    protected static function addPath(string $path = null)
    {
        return $path !== null ? DIRECTORY_SEPARATOR . ltrim($path, '\/') : '';
    }
}