<?php

namespace Krugozor\Framework\Module\Advert\Controller;

class FrontendAdd extends FrontendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
};