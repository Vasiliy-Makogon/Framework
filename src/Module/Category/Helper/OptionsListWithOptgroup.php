<?php

namespace Krugozor\Framework\Module\Category\Helper;

use Krugozor\Framework\Helper\Form;
use Krugozor\Cover\CoverArray;

/**
 * На основании объекта CoverArray, который является деревом категорий,
 * создает options списка с optgroup.
 */
class OptionsListWithOptgroup
{
    /**
     * @var CoverArray
     */
    private $tree;

    /**
     * @param CoverArray $tree
     */
    public function __construct(CoverArray $tree)
    {
        $this->tree = $tree;
    }

    /**
     * @return CoverArray
     */
    public function getOptions(): CoverArray
    {
        return $this->createOptionsList($this->tree);
    }

    /**
     * @param CoverArray $tree
     * @return CoverArray
     */
    protected function createOptionsList(CoverArray $tree): CoverArray
    {
        $categories = new CoverArray();

        foreach ($tree as $category) {
            if ($category->getPid() == 0) {
                $optgroup = Form::inputOptgroup($category->getName());

                if ($category->getTree() && $category->getTree()->count()) {
                    foreach ($this->createOptionsList($category->getTree()) as $element) {
                        $optgroup->addOption($element);
                    }
                }

                $categories->append($optgroup);
            } else {
                $params = array(
                    'data-pid' => $category->getPid(),
                    'data-haschilds' => $category->getChildsAsArray() ? '1' : '0',
                );

                $option = Form::inputOption($category->getId(), $category->getNameForOptionElement(), $params);

                $categories->append($option);

                if ($category->getTree() && $category->getTree()->count()) {
                    foreach ($this->createOptionsList($category->getTree()) as $element) {
                        $categories->append($element);
                    }
                }
            }
        }

        return $categories;
    }
}