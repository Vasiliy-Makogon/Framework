<?php

namespace Krugozor\Framework\Module\Category\Helper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Helper\HelperAbstract;

class BreadCrumbs extends HelperAbstract
{
    // дерево
    private $tree;

    // Префикс каждого узла URL
    private $prefix_url;

    // Делать ли последний элемент BreadCrumbs ссылкой
    private $last_link = false;

    // разделитель элементов хлебных крошек
    private $separator;

    // Добавлять ли перед хлебными крошками символ $this->separator
    private $add_first_separator = true;

    // Вывести хлебные крошки как простой текст, без HTML.
    private $only_plain_text = false;

    // Строка, добавляемая в конец строки последнего элемента хлебных крошек.
    private $postfix_text;

    /**
     * BreadCrumbs constructor.
     * @param CoverArray $tree
     * @param string $prefix_url
     * @param string $separator
     */
    public function __construct(CoverArray $tree, string $prefix_url = '', string $separator = '&raquo;')
    {
        $this->tree = $tree;
        $this->prefix_url = $prefix_url;
        $this->separator = $separator;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return ($this->add_first_separator ? ' ' . $this->separator . ' ' : '') . $this->createBreadCrumbs($this->tree);
    }

    /**
     * @param $string
     * @return BreadCrumbs
     */
    public function setPostfixText($string): self
    {
        $this->postfix_text = $string;

        return $this;
    }

    /**
     * Если параметр установлен в TRUE, перед хлебными крошками будет добавлен символ $this->separator
     *
     * @param bool $value
     * @return BreadCrumbs
     */
    public function addFirstSeparator(bool $value): self
    {
        $this->add_first_separator = $value;

        return $this;
    }

    /**
     * true, если выводить хлебные крошки как простой текст.
     *
     * @param bool $value
     * @return BreadCrumbs
     */
    public function setOnlyPlainText(bool $value): self
    {
        $this->only_plain_text = $value;

        return $this;
    }

    /**
     * Если параметр установлен в TRUE, последний элемент хлебных крошек будет ссылкой.
     *
     * @param bool $value
     * @return BreadCrumbs
     */
    public function lastElementIsLink(bool $value): self
    {
        $this->last_link = $value;

        return $this;
    }

    /**
     * @param CoverArray $tree
     * @return string
     */
    private function createBreadCrumbs(CoverArray $tree): string
    {
        if (!$tree->count()) {
            return '';
        }

        $str = '';

        foreach ($tree as $category) {
            if ($category->getTree() && $category->getTree()->count()) {
                if ($this->only_plain_text) {
                    $str .= $category->getName() . ' ' . $this->separator . ' ';
                } else {
                    $str .= '<a href="' . $this->prefix_url . $category->getUrl() . '">' . $category->getName() . '</a> ' . $this->separator . ' ';
                }

                $str .= $this->createBreadCrumbs($category->getTree());
            } else {
                $str .= $this->last_link
                    ? '<a href="' . $this->prefix_url . $category->getUrl() . '">' . $category->getName() . $this->postfix_text . '</a>'
                    : $category->getName() . $this->postfix_text;
            }
        }

        return $str;
    }
}