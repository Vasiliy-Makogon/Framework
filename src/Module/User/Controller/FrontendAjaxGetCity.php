<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller\Ajax;

class FrontendAjaxGetCity extends Ajax
{
    public function run()
    {
        $this->getView('Ajax');

        $this->getView()->locations = $this->getMapper('User/City')->getListForSelectOptions(
            $this->getRequest()->getRequest('id')
        );

        return $this->getView();
    }
}