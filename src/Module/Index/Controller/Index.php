<?php

namespace Krugozor\Framework\Module\Index\Controller;

use Krugozor\Framework\Controller;

class Index extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral'
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->online_users = $this->getMapper('User/User')->findUsersOnline();

        return $this->getView();
    }
}