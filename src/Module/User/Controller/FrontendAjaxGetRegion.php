<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller\Ajax;

class FrontendAjaxGetRegion extends Ajax
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