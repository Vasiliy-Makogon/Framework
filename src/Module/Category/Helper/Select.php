<?php
/**
 * На основании объекта Krugozor_Cover_Array,
 * который является деревом категорий, создает select-список.
 */
class Krugozor_Module_Category_Helper_Select extends Krugozor_Helper_Abstract
{
    private $tree;

    public function __construct(Krugozor_Cover_Array $tree)
    {
        $this->tree = $tree;
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Helper_Abstract::getHtml()
     */
    public function getHtml()
    {
        return $this->createSelect($this->tree);
    }

    /**
     * Создает "объектный" select-список.
     *
     * @param Krugozor_Cover_Array $tree
     * @return Krugozor_Cover_Array
     */
    protected function createSelect(Krugozor_Cover_Array $tree)
    {
        $categories = new Krugozor_Cover_Array();

        foreach ($tree as $category)
        {
            if ($category->getPid() == 0)
            {
                $optgroup = Krugozor_Helper_Form::inputOptgroup($category->getName());

                if ($category->getTree() && $category->getTree()->count())
                {
                    foreach ($this->createSelect($category->getTree()) as $element)
                    {
                        $optgroup->addOption($element);
                    }
                }

                $categories->append($optgroup);
            }
            else
            {
                $params = array(
                    'data-pid' => $category->getPid(),
                    'data-haschilds' => $category->getChildsAsArray() ? '1' : '0',
                );

                $option = Krugozor_Helper_Form::inputOption($category->getId(), $category->getNameForOptionElement(), $params);

                $categories->append($option);

                if ($category->getTree() && $category->getTree()->count())
                {
                    foreach ($this->createSelect($category->getTree()) as $element)
                    {
                        $categories->append($element);
                    }
                }
            }
        }

        return $categories;
    }
}