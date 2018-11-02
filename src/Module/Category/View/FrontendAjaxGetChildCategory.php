<?php

namespace Krugozor\Framework\Module\Category\View;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\View\Ajax;

class FrontendAjaxGetChildCategory extends Ajax
{
    /**
     * На основании объектов категорий строит новый массив
     * с необходимыми для Ajax-ответа значениями.
     */
    protected function createChildCategory()
    {
        $result = array();

        if ($this->categories && is_object($this->categories) && $this->categories instanceof CoverArray) {
            foreach ($this->categories as $category) {
                $result[] = array(
                    'id' => $category->getId(),
                    'pid' => $category->getPid(),
                    'haschilds' => ($category->getChildsAsArray() ? '1' : '0'),
                    'name' => $category->getNameForOptionElement(),
                );
            }
        }

        unset($this->getStorage()->categories);
        $this->getStorage()->setData($result);
    }

    /**
     * @param null|array $data
     * @return string
     */
    protected function createJson(?array $data = null): string
    {
        $this->createChildCategory();
        return parent::createJson();
    }
}