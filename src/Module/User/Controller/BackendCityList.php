<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Module\User\Service\CityList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendCityList extends BackendCityCommon
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

        $list = new CityList(
            $this->getRequest(),
            $this->getMapper('User/City'),
            Adapter::getManager($this->getRequest(), 20, 10)
        );

        $this->getView()->cityList = $list->findList();
        $this->getView()->regionList = $this->getMapper('User/Region')->getListActiveRegion();

        return $this->getView();
    }
}