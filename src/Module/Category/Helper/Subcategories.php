<?php

namespace Krugozor\Framework\Module\Category\Helper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Helper\HelperAbstract;
use Krugozor\Framework\Module\Category\Model\Category;

/**
 * Создание простого, древовидного списка подкатегорий раздела на основании дерева.
 */
class Subcategories extends HelperAbstract
{
    /**
     * Дерево категорий.
     *
     * @var CoverArray
     */
    protected $tree;

    /**
     * Префикс URL-адреса категорий.
     *
     * @var string
     */
    protected $prefix_url;

    /**
     * Текущая категория.
     *
     * @var Category
     */
    protected $current_category;

    /**
     * Subcategories constructor.
     * @param CoverArray $tree
     * @param string $prefix_url
     */
    public function __construct(CoverArray $tree, string $prefix_url = '')
    {
        $this->tree = $tree;
        $this->prefix_url = $prefix_url;
    }

    /**
     * @return string
     */
    public function getHtml(): string
    {
        return $this->createSubcategories($this->tree);
    }

    /**
     * Устанавливает текущую категорию, если необходимо в списке категорий выделить её особо.
     *
     * @param Category $current_category
     * @return Subcategories
     */
    public function setCurrentCategory(Category $current_category): self
    {
        $this->current_category = $current_category;

        return $this;
    }

    /**
     * Создает список категорий на основе дерева категорий $tree.
     *
     * @param CoverArray $tree
     * @return string
     */
    protected function createSubcategories(CoverArray $tree): string
    {
        if (!$tree->count()) {
            return '';
        }

        $str = '<ul>';

        foreach ($tree as $category) {
            $str .= '<li><a href="' . $this->prefix_url . $category->getUrl() . '">' .
                $category->getName() . '</a>&nbsp;<span>[' . $category->getAdvertCount() . ']</span>';

            $str .= $this->createSubcategories($category->getTree()) . '</li>';
        }

        $str .= '</ul>';

        return $str;
    }
}