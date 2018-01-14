<?php

class Krugozor_Cover_Array extends Krugozor_Cover_Abstract_Array
{
    /**
     * @param array $data
     */
    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            $this->data[$key] = $this->array2cover($value);
        }
    }
}