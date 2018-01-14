<?php

class Krugozor_Http_Cover_Data_Request extends Krugozor_Http_Cover_Data
{
    /**
     * (non-PHPdoc)
     * @see Krugozor/Cover/Abstract/Krugozor_Cover_Abstract_Array::__set()
     */
    public function __set($key, $value)
    {
        parent::__set($key, $value);

        $_REQUEST = $this->getDataAsArray();
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor/Cover/Abstract/Krugozor_Cover_Abstract_Simple::__unset()
     */
    public function __unset($key)
    {
        parent::__unset($key);

        $_REQUEST = $this->getDataAsArray();
    }
}