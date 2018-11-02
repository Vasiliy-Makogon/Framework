<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Module\User\Service\RegionList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendRegionList extends BackendRegionCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            $message = $this->getView()->getLang()['notification']['message']['forbidden_access'];
            return $this->createNotification()
                ->setMessage($message)
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new RegionList(
            $this->getRequest(),
            $this->getMapper('User/Region'),
            Adapter::getManager($this->getRequest(), 20, 10)
        );

        $this->getView()->regionList = $list->findList();
        $this->getView()->countryList = $this->getMapper('User/Country')->getListActiveCountry();

        return $this->getView();
    }
}