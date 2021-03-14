<?php

namespace Krugozor\Framework\Html;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Helper\HelperAbstract;

class Title extends HelperAbstract
{
    /**
     * @var string
     */
    const SEPARATOR_VERTICAL_LINE = '|';

    /**
     * @var string
     */
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
     * @var CoverArray
     */
    private $data;

    /**
     * @var Title
     */
    private static $instance;

    /**
     * @param string $separator
     * @return Title
     */
    public static function getInstance($separator = self::SEPARATOR_VERTICAL_LINE)
    {
        if (!self::$instance) {
            $separator = trim($separator);

            if (empty($separator)) {
                throw new \DomainException(__METHOD__ . ': Не задан сепаратор');
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
        $this->separator = $separator;
        $this->data = new CoverArray();
    }

    /**
     * Возвращает кол-во составных частей тега title.
     *
     * @return int
     */
    public function getCountElements()
    {
        return $this->data->count();
    }

    /**
     * Добавляет элемент хлебных крошек title
     *
     * @return $this
     */
    public function add()
    {
        foreach (func_get_args() as $value) {
            if (!is_scalar($value)) {
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
     * Добавляет постфикс к последнему элементу тега title
     *
     * @param string $postfix
     * @return $this
     */
    public function addPostfixInLastElement($postfix)
    {
        $last_element_value = $this->data->getLast();
        if (is_null($last_element_value)) {
            return $this;
        }

        $postfix = ' ' . trim($postfix);
        $last_element_value = rtrim($last_element_value, '.,!?:;');

        $this->data[$this->data->count() -1] = $last_element_value . $postfix;

        return $this;
    }

    /**
     * Возвращает последний элемент в коллекции title.
     *
     * @param bool $escape
     * @return mixed|string
     */
    public function getLastElement($escape = false)
    {
        return $escape
            ? $this->escape($this->data->getLast())
            : $this->data->getLast();
    }

    /**
     * Возвращает первый элемент в коллекции title.
     *
     * @param bool $escape
     * @return mixed|string
     */
    public function getFirstElement($escape = false)
    {
        return $escape
            ? $this->escape($this->data->getFirst())
            : $this->data->getFirst();
    }

    /**
     * Удаляет элемент составных частей тега title под индексом $index.
     *
     * @param int
     */
    public function deleteByIndex($index)
    {
        unset($this->data[$index]);
    }

    /**
     * Возвращает элемент составных частей тега title под индексом $index.
     *
     * @param int $index
     * @param bool $escape
     * @return mixed|null
     */
    public function getByIndex($index, $escape = false)
    {
        $value = $this->data->item($index);

        return $value && $escape ? $this->escape($value) : $value;
    }

    /**
     * Возвращает строку для подстановки в тег html title
     *
     * @param string
     */
    public function getTitle()
    {
        $title = implode(" $this->separator ", $this->data->reverse());

        return $this->escape($title);
    }

    /**
     * Возвращает html-код тега title.
     *
     * @return string
     */
    public function getHtml(): string
    {
        return '<title>' . $this->getTitle() . '</title>' . PHP_EOL;
    }

    /**
     * Возвращает html-код тега title для Open Graph.
     *
     * @return string
     */
    public function getOgHtml(): string
    {
        return '<meta property="og:title" content="' . $this->getTitle() . '" />' . PHP_EOL;
    }

    /**
     * @param string $value
     * @return string
     */
    private function escape($value)
    {
        return htmlspecialchars((string)$value,ENT_QUOTES | ENT_HTML5, 'UTF-8', false);
    }
}