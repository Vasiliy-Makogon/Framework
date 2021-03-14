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
     * @var array
     */
    private $data = array();

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
    }

    /**
     * Возвращает кол-во составных частей тега title.
     *
     * @return int
     */
    public function getCountElements()
    {
        return count($this->data);
    }

    /**
     * Добавляет элемент хлебных крошек title
     *
     * @return $this
     */
    public function add()
    {
        foreach (func_get_args() as $value) {
            if (is_object($value) && $value instanceof CoverArray) {
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
     * @return $this
     */
    public function addPostfixInLastElement($postfix)
    {
        $this->data[$this->getCountElements() - 1] =
            rtrim($this->data[$this->getCountElements() - 1], '.,!?:;') .
            $postfix;

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
     */
    public function getTitle()
    {
        return htmlspecialchars(implode(" $this->separator ", array_reverse($this->data)), ENT_QUOTES);
    }

    /**
     * Возвращает html-код тега title.
     *
     * @return string
     */
    public function getHtml(): string
    {
        return '<title>' . $this->getTitle() . '</title>';
    }

    /**
     * Возвращает html-код тега title для Open Graph.
     *
     * @return string
     */
    public function getOgHtml(): string
    {
        return '<meta property="og:title" content="' . $this->getTitle() . '"/>';
    }
}