<?php

namespace Krugozor\Framework\Http\Cover\Data;

use Krugozor\Framework\Http\Cover\Data;

class Get extends Data
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        parent::__set($key, $value);

        $_GET = $this->getDataAsArray();
    }

    /**
     * @param string $key
     */
    public function __unset(string $key)
    {
        parent::__unset($key);

        $_GET = $this->getDataAsArray();
    }
}