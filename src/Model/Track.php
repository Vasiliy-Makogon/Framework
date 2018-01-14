<?php

/**
 * Объект, содержащий данные модели при первичном наполнении и позволяющий при записи данных в СУБД ослеживать,
 * какие свойства были изменены, а какие - нет. Данный объект наполняется в момент наполнения объекта модели
 * и его свойства не меняются в ходе работы клиентского кода.
 */
class Krugozor_Model_Track extends Krugozor_Cover_Abstract_Simple implements Countable
{
    public function __set($key, $value)
    {
        //if (!(isset($this->data[$key]) || in_array($key, array_keys($this->data))))
        {
            $this->data[$key] = $value;
        }
    }

    /**
     * Сверяет значение $value со значением $this->data[$key].
     * Если значения равны, то возвращает true, false - в ином случае.
     *
     * @param string $key
     * @param mixed $value
     * @return boolean
     */
    public function compare($key, $value)
    {
        if (is_object($value) && is_object($this->data[$key])) {
            return $value == $this->data[$key];
        } else {
            /*
                Отсутствующие значения и проблемы связанные с NULL и пустой строкой.

                Если объект достали из СУБД, в track записались свойства с NULL значением.
                Пришли данные из POST-запроса с пустыми строками. Track при сравнении сочтет, что пустая строка -
                это значение отличное от NULL и даст добро на обновление данных.
                Но в базу опять запишется NULL, т.к. Mapper преобразует пустые строки в NULL.
                Получится лишний запрос, который в поле со значением NULL проставит в NULL.
                Пример такого поведеня:

                // Нашли объект с пустым свойством last_name (поле в таблице в NULL)
                $object = $this->getMapper('User/User')->findModelById(3801);
                var_dump($object->getLastName()); // NULL
                echo "---------\n";
                $object->setLastName('');
                var_dump($object->getLastName()); // string(0) ""
                echo "---------\n";
                // save не сработает благодаря конструкции ниже
                $this->getMapper('User/User')->saveModel($object);
                echo "Query: " . $this->getMapper('User/User')->getDb()->getQueryString();

                Для предотвращения этой ситуации код ниже.
            */
            if ($value === '') {
                $value = null;
            }

            return $value === $this->data[$key];
        }
    }

    /**
     * Реализация интерфейса Countable
     *
     * @param void
     * @return int
     */
    final public function count()
    {
        return count($this->data);
    }
}