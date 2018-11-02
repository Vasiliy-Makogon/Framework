<?php

namespace Krugozor\Framework\Module\Help\Controller;

use Krugozor\Framework\Controller;

class TermsOfService extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral'
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
}