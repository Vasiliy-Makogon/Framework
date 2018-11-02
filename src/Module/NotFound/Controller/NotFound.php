<?php

namespace Krugozor\Framework\Module\NotFound\Controller;

use Krugozor\Framework\Controller;

class NotFound extends Controller
{
    public function run()
    {
        $this->getResponse()->setHttpStatusCode(404);
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
}