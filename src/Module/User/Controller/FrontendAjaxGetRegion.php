<?php

class Krugozor_Module_User_Controller_FrontendAjaxGetRegion extends Krugozor_Controller_Ajax
{
    public function run()
    {
        $this->getView('Ajax');

        $this->getView()->locations = $this->getMapper('User/Region')->getListForSelectOptions(
            $this->getRequest()->getRequest('id')
        );

        return $this->getView();
    }
}