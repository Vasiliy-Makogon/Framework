<?php
class Krugozor_Module_Category_Controller_FrontendAjaxGetChildCategory extends Krugozor_Controller_Ajax
{
    public function run()
    {
        $this->getView('FrontendAjaxGetChildCategory', 'Krugozor_Module_Category_View_FrontendAjaxGetChildCategory');

        $this->getView()->categories = $this->getMapper('Category/Category')->getActiveChildCategories(
            $this->getRequest()->getRequest('id')
        );

        return $this->getView();
    }
}