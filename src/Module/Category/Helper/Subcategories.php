<?php
/**
 * Создание простого, древовидного списка подкатегорий раздела на основании дерева.
 */
class Krugozor_Module_Category_Helper_Subcategories extends Krugozor_Helper_Abstract
{
    /**
     * Дерево категорий.
     *
     * @var Krugozor_Cover_Array
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
     * @var Krugozor_Module_Category_Model_Category
     */
    protected $current_category;

    /**
     * @param Krugozor_Cover_Array $tree
     * @param string $prefix_url
     */
    public function __construct(Krugozor_Cover_Array $tree, $prefix_url='')
    {
        $this->tree = $tree;
        $this->prefix_url = $prefix_url;
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Helper_Abstract::getHtml()
     */
    public function getHtml()
    {
        return $this->createSubcategories($this->tree);
    }

    /**
     * Устанавливает текущую категорию, если необходимо в списке категорий выделить её особо.
     *
     * @param Krugozor_Module_Category_Model_Category $category
     * @return Krugozor_Module_Category_Helper_SubcategoriesCols
     */
    public function setCurrentCategory(Krugozor_Module_Category_Model_Category $current_category)
    {
        $this->current_category = $current_category;

        return $this;
    }

    /**
     * Создает список категорий на основе дерева категорий $tree.
     *
     * @param Krugozor_Cover_Array $tree
     * @return string
     */
    protected function createSubcategories(Krugozor_Cover_Array $tree)
    {
        if (!$tree->count())
        {
            return '';
        }

        $str = '<ul>';

        foreach ($tree as $category)
        {
            $str .= '<li><a href="' . $this->prefix_url . $category->getUrl() . '">' .
                    $category->getName() . '</a>&nbsp;<span>[' . $category->getAdvertCount() . ']</span>';

            $str .= $this->createSubcategories($category->getTree()).'</li>';
        }

        $str .= '</ul>';

        return $str;
    }
}