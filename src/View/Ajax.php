<?php

class Krugozor_View_Ajax extends Krugozor_View
{
    /**
     * В некоторых случаях не достаточно вызова магических методов в контексте контроллера, что бы
     * работать полноценно с хранилищем View. Например, в ситуации с JSON-ответом, когда ответ должен состоять из
     * объекта данных списочного типа. В этом случае в контроллере вызывается
     * $this->getStorage()->setData($result);
     * что бы можно было пользоваться всеми возможностями Krugozor_Cover_Array
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public final function getStorage()
    {
        return $this->data;
    }

    /**
     * Возвращает JSON-представление $data или $this->data.
     *
     * @param mixed
     * @return string
     */
    protected function createJson($data = null)
    {
        return json_encode($data !== null ? $data : $this->data->getDataAsArray(), JSON_FORCE_OBJECT);
    }
}