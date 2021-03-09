<?php

namespace Krugozor\Framework\Module\Group\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Group\Service\GroupList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendMain extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            'Group/BackendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new GroupList(
            $this->getRequest(),
            $this->getMapper('Group/Group'),
            Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->groupList = $list->findList();
        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
}