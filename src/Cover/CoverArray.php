<?php

namespace Krugozor\Framework\Cover;

class CoverArray extends AbstractArray
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