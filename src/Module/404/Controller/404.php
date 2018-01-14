<?php
class Krugozor_Module_404_Controller_404 extends Krugozor_Controller
{
    public function run()
    {
        $this->getResponse()->setHttpStatusCode(404);
        $this->getView()->getLang()->loadI18n(
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
}