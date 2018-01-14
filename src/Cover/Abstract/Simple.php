<?php

/**
 * Данный класс является базовым для многих классов системы.
 */
abstract class Krugozor_Cover_Abstract_Simple
{
    protected $data = array();

    public function __get($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    public function __set($key, $value)
    {
        $this->data[$key] = $value;
    }

    public function __isset($key)
    {
        return isset($this->data[$key]);
    }

    public function __unset($key)
    {
        unset($this->data[$key]);
    }

    public function getData()
    {
        return $this->data;
    }

    public function setData($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }

        return $this;
    }

    public function clear()
    {
        $this->data = array();

        return $this;
    }
}