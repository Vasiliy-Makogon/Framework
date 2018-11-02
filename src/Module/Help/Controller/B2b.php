<?php
class Krugozor_Module_Help_Controller_B2b extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/FrontendGeneral')->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
}