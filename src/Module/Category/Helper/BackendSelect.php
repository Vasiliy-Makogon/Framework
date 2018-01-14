<?php
/**
 * Отличается от Krugozor_Module_Category_Helper_Select тем, что не создает optgroup.
 */
class Krugozor_Module_Category_Helper_BackendSelect extends Krugozor_Module_Category_Helper_Select
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Module_Category_Helper_Select::createSelect()
     */
    protected function createSelect(Krugozor_Cover_Array $tree)
    {
        $categories = new Krugozor_Cover_Array();

        foreach ($tree as $category) {
            $option = Krugozor_Helper_Form::inputOption($category->getId(), $category->getNameForOptionElement());
            $categories->append($option);

            if ($category->getTree() && $category->getTree()->count()) {
                foreach ($this->createSelect($category->getTree()) as $element) {
                    $categories->append($element);
                }
            }
        }

        return $categories;
    }
}