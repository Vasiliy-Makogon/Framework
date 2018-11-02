<?php

namespace Krugozor\Framework\View;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\View;

class Ajax extends View
{
    /**
     * В некоторых случаях не достаточно вызова магических методов в контексте контроллера, что бы
     * работать полноценно с хранилищем View.
     * Например, в ситуации с JSON-ответом, когда ответ должен состоять из объекта данных списочного типа.
     * В этом случае в контроллере вызывается
     * $this->getView()->getStorage()->clear()->setData($data);
     * что бы можно было пользоваться всеми возможностями CoverArray
     *
     * @return CoverArray
     */
    public final function getStorage(): CoverArray
    {
        return $this->data;
    }

    /**
     * Возвращает JSON-представление $data или $this->data.
     *
     * @param null|array $data
     * @return string
     */
    protected function createJson(?array $data = null): string
    {
        return json_encode($data !== null ? $data : $this->data->getDataAsArray(), JSON_FORCE_OBJECT);
    }
}