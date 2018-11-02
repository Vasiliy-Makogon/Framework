<?php

namespace Krugozor\Framework\Http\Cover\Data;

use Krugozor\Framework\Http\Cover\Data;

class Request extends Data
{
    /**
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        parent::__set($key, $value);

        $_REQUEST = $this->getDataAsArray();
    }

    /**
     * @param string $key
     */
    public function __unset(string $key)
    {
        parent::__unset($key);

        $_REQUEST = $this->getDataAsArray();
    }
}