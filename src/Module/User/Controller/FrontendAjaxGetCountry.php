<?php

class Krugozor_Module_User_Controller_FrontendAjaxGetCountry extends Krugozor_Controller_Ajax
{
    public function run()
    {
        $this->getView('Ajax');

        $this->getView()->locations = $this->getMapper('User/Country')->getListForSelectOptions();

        return $this->getView();
    }
}