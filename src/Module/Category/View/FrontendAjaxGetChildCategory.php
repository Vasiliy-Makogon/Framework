<?php
class Krugozor_Module_Category_View_FrontendAjaxGetChildCategory extends Krugozor_View_Ajax
{
    /**
     * На основании объектов категорий строит новый массив
     * с необходимыми для Ajax-ответа значениями.
     *
     * @param void
     * @return string
     */
    protected function createChildCategory()
    {
        $result = array();
        $index = 0;

        if ($this->categories && is_object($this->categories) && $this->categories instanceof Krugozor_Cover_Array)
        {
            foreach ($this->categories as $category)
            {
                $result[$index++] = array(
                    'id' => $category->getId(),
                    'pid' => $category->getPid(),
                    'haschilds' => ($category->getChildsAsArray() ? '1' : '0'),
                    'name' => $category->getNameForOptionElement(),
                );
            }
        }

        // categories - временная переменная, больше не нужна
        unset($this->getStorage()->categories);
        // ответ должен состоять из одного многомерного объекта данных
        $this->getStorage()->setData($result);
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_View_Ajax::createJson()
     */
    protected function createJson($data=null)
    {
        $this->createChildCategory();
        return parent::createJson();
    }
}