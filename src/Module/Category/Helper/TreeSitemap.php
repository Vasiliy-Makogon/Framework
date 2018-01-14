<?php
class Krugozor_Module_Category_Helper_TreeSitemap extends Krugozor_Module_Category_Helper_Subcategories
{
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
                    $category->getName() . '</a>';

            $str .= $this->createSubcategories($category->getTree()).'</li>';
        }

        $str .= '</ul>';

        return $str;
    }
}