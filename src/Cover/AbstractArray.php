<?php

namespace Krugozor\Framework\Cover;

/**
 * Обёртка массива, хранилище.
 * Попытка реализации объекта для более удобной работы с массиво-образной структурой данных.
 */
abstract class AbstractArray extends SimpleArray implements IteratorAggregate, Countable, ArrayAccess, Serializable
{
    /**
     * @return string
     */
    public function __toString()
    {
        return '';
    }

    /**
     * @param string $key ключ
     * @param mixed $value значение
     * @see Krugozor_Cover_Abstract_Simple::__set()
     */
    public function __set($key, $value)
    {
        $this->data[$key] = $this->array2cover($value);
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

    /**
     * Реализация интерфейса IteratorAggregate
     *
     * @param void
     * @return ArrayIterator
     */
    final public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Возвращает элемент коллекции с заданным индексом в качестве результата.
     * Аналог parent::__get, но предназначен для числовых индексов.
     *
     * @param mixed
     * @return mixed
     */
    final public function item($key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Присоединяет один элемент в начало массива.
     *
     * @param mixed
     * @return Krugozor_Cover_Abstract_Array
     */
    final public function prepend($value)
    {
        array_unshift($this->data, $this->array2cover($value));

        return $this;
    }

    /**
     * Присоединяет один элемент в конец массива.
     *
     * @param mixed
     * @return Krugozor_Cover_Abstract_Array
     */
    final public function append($value)
    {
        array_push($this->data, $this->array2cover($value));

        return $this;
    }

    /**
     * Возвращает последний элемент массива.
     *
     * @param void
     * @return mixed
     */
    final public function getLast()
    {
        $last = end($this->data);
        reset($this->data);

        return $last;
    }

    /**
     * Возвращает первый элемент массива.
     *
     * @param void
     * @return mixed
     */
    final public function getFirst()
    {
        $last = end($this->data);
        reset($this->data);

        return $last;
    }

    /**
     * Возвращает данные объекта как массив.
     *
     * @param void
     * @return array
     */
    final public function getDataAsArray()
    {
        return self::object2array($this->data);
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * @param mixed $key ключ элемента
     * @param mixed $value значение элемента
     * @return Krugozor_Cover_Array
     */
    final public function offsetSet($key, $value)
    {
        // Это присвоение нового элемента массиву типа $var[] = 'element';
        if ($key === null) {
            $u = &$this->data[];
        } else {
            $u = &$this->data[$key];
        }

        $u = $this->array2cover($value);
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * @param int|string ключ элемента
     * @return boolean
     */
    final public function offsetExists($key)
    {
        return isset($this->data[$key]);
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     *
     * @param int|string ключ элемента
     * @return void
     */
    final public function offsetUnset($key)
    {
        if (isset($this->data[$key])) {
            unset($this->data[$key]);
        }
    }

    /**
     * Реализация метода интерфейса ArrayAccess.
     * Отличается от поведения ArrayObject тем, что в случае отсутствия
     * запрошеного элемента не генерирует ошибку, а создает в вызвавшем
     * его объекте, в хранилище, свойство $key содержащее пустой объект текущего типа.
     *
     * Это поведение изначально было предназначено для view, когда в шаблоне можно писать
     * переменные, которые могут быть не объявлены. Например:
     * echo $data['non_exists']['var']; // пустая строка - сработал __toString()
     *
     * Или можно объявлять цепочку вложенных элементов:
     * $array['non_exists_prop']['non_exists_prop']['property'] = true;
     * - здесь будут созданы вложенные объекты, т.е. условие
     * non_exists_prop->non_exists_prop->property == true
     * будет истинно.
     *
     * @param int|string ключ элемента
     * @return Krugozor_Cover_Abstract_Array
     */
    final public function offsetGet($key)
    {
        if (!isset($this->data[$key])) {
            $this->data[$key] = new $this();
        }

        return $this->data[$key];
    }

    /**
     * Реализация метода интерфейса Serializable.
     *
     * @return string
     */
    final public function serialize()
    {
        return serialize($this->data);
    }

    /**
     * Реализация метода интерфейса Serializable.
     *
     * @param array $data
     * @return Krugozor_Cover_Abstract_Array
     */
    final public function unserialize($data)
    {
        $this->setData(unserialize($data));

        return $this;
    }

    /**
     * Преобразует все значения массива $in в массивы, если значения
     * каких-либо элементов данных будут объекты текущего типа.
     *
     * @param array
     * @return array
     */
    final protected static function object2array(array $in)
    {
        foreach ($in as $key => $value) {
            $in[$key] = (is_object($value) && $value instanceof self)
                ? $in[$key] = self::object2array($value->getData())
                : $value;
        }

        return $in;
    }

    /**
     * Возвращает объект текущего типа, если переданным
     * в метод значением является массив.
     *
     * @param mixed
     * @return mixed
     */
    final protected function array2cover($value)
    {
        return is_array($value) ? new $this($value) : $value;
    }
}