<?php

class Krugozor_Http_Cover_Data_Get extends Krugozor_Http_Cover_Data
{
    /**
     * (non-PHPdoc)
     * @see Krugozor/Cover/Abstract/Krugozor_Cover_Abstract_Array::__set()
     */
    public function __set($key, $value)
    {
        parent::__set($key, $value);

        $_GET = $this->getDataAsArray();
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor/Cover/Abstract/Krugozor_Cover_Abstract_Simple::__unset()
     */
    public function __unset($key)
    {
        parent::__unset($key);

        $_GET = $this->getDataAsArray();
    }
}