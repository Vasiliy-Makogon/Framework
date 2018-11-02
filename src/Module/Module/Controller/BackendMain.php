<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Module\Service\ModuleList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendMain extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new ModuleList(
            $this->getRequest(),
            $this->getMapper('Module/Module'),
            Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->modulesList = $list->findList();

        return $this->getView();
    }
}