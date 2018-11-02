<?php

namespace Krugozor\Framework\Module\Authorization\Controller;

use Krugozor\Framework\Authorization;
use Krugozor\Framework\Controller;

/**
 * Выход из системы.
 */
class Logout extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        if (!$this->getCurrentUser()->isGuest()) {
            $auth = new Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
            $auth->logout();
        }

        return $this->createNotification()
            ->setMessage($this->getView()->getLang()['notification']['message']['outside_system'])
            ->setNotificationUrl($this->getRequest()->getRequest('referer') ?: '/')
            ->run();
    }
}