<?php
class Krugozor_Module_Help_Controller_Sale extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/FrontendGeneral')->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->categories = $this->getMapper('Category/Category')->findCategoriesFirstLevel();

        return $this->getView();
    }
}