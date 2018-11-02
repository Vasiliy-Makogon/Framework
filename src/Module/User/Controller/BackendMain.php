<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Module\User\Service\UserList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendMain extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', 'User/BackendCommon', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            $message = $this->getView()->getLang()['notification']['message']['forbidden_access'];
            return $this->createNotification()
                ->setMessage($message)
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new UserList(
            $this->getRequest(),
            $this->getMapper('User/User'),
            Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->usersList = $list->findList();

        $this->getView()->search = $this->getRequest()->getRequest('search');
        $this->getView()->col = $this->getRequest()->getRequest('col');
        $this->getView()->user_active = $this->getRequest()->getRequest('user_active');

        $this->getView()->user_country = $this->getRequest()->getRequest('user_country');
        $this->getView()->user_region = $this->getRequest()->getRequest('user_region');
        $this->getView()->user_city = $this->getRequest()->getRequest('user_city');

        return $this->getView();
    }
}