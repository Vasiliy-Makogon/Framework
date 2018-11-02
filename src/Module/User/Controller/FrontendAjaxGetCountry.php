<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller\Ajax;

class FrontendAjaxGetCountry extends Ajax
{
    public function run()
    {
        $this->getView('Ajax');

        $this->getView()->locations = $this->getMapper('User/Country')->getListForSelectOptions();

        return $this->getView();
    }
}