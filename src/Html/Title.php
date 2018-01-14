<?php

class Krugozor_Html_Title
{
    /**
     * Типы сепараторов разделения составных частей тега title.
     *
     * @var string
     */
    const SEPARATOR_VERTICAL_LINE = '|';
    const SEPARATOR_FORWARD_SLASH = '/';

    /**
     * Актуальный сепаратор разделения составных частей тега title.
     *
     * @var string
     */
    private $separator;

    /**
     * Составные части тега title.
     *
     * @var array
     */
    private $data = array();

    /**
     * @var Krugozor_Html_Title
     */
    private static $instance;

    /**
     * @param string сепаратор
     */
    public static function getInstance($separator = self::SEPARATOR_VERTICAL_LINE)
    {
        if (!self::$instance) {
            $separator = trim($separator);

            if ($separator === '') {
                throw new DomainException(__METHOD__ . ': Не задан сепаратор');
            }

            self::$instance = new self($separator);
        }

        return self::$instance;
    }

    /**
     * @param string $separator сепаратор
     */
    private function __construct($separator)
    {
        $this->separator = " $separator ";
    }

    /**
     * Возвращает кол-во составных частей тега title.
     *
     * @param void
     * @return int
     */
    public function getCountElements()
    {
        return count($this->data);
    }

    /**
     * Добавляет элемент хлебных крошек title
     *
     * @param mixed
     * @return void
     */
    public function add()
    {
        foreach (func_get_args() as $value) {
            if (is_object($value) && $value instanceof Krugozor_Cover_Array) {
                $value = $value->getData();
            }

            if (is_array($value)) {
                foreach ($value as $element) {
                    if ($element = strip_tags($element)) {
                        $this->data[] = $element;
                    }
                }
            } else {
                if ($value = strip_tags($value)) {
                    $this->data[] = $value;
                }
            }
        }

        return $this;
    }

    /**
     * Добавляет постфикс к первому элементу тега title
     *
     * @param string $postfix
     * @return Krugozor_Html_Title
     */
    public function addPostfixInLastElement($postfix)
    {
        $this->data[$this->getCountElements() - 1] = rtrim($this->data[$this->getCountElements() - 1], '.,!?:;') . $postfix;

        return $this;
    }

    /**
     * Возвращает последний элемент в коллекции title.
     *
     * @return string
     */
    public function getLastElement()
    {
        return $this->data[$this->getCountElements() - 1];
    }

    /**
     * Удаляет элемент составных частей тега title под индексом $index.
     *
     * @param int
     * @return void
     */
    public function deleteByIndex($index)
    {
        unset($this->data[$index]);
    }

    /**
     * Возвращает элемент составных частей тега title под индексом $index.
     *
     * @param int
     * @return string
     */
    public function getByIndex($index)
    {
        return isset($this->data[$index]) ? $this->data[$index] : null;
    }

    /**
     * Возвращает строку для подстановки в тег html title
     *
     * @param string
     * @return void
     */
    public function getTitle()
    {
        return htmlspecialchars(implode($this->separator, array_reverse($this->data)), ENT_QUOTES);
    }

    /**
     * Возвращает html-код тега title.
     *
     * @param void
     * @return string
     */
    public function getHtml()
    {
        return '<title>' . $this->getTitle() . '</title>';
    }
}