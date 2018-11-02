<?php

namespace Krugozor\Framework\Type;

interface TypeInterface
{
    /**
     * Возвращает скалярное значение типа.
     *
     * @return mixed
     */
    public function getValue();
}