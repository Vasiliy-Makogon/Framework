<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Module\User\Service\CountryList;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class BackendCountryList extends BackendCountryCommon
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

        $list = new CountryList(
            $this->getRequest(),
            $this->getMapper('User/Country'),
            Adapter::getManager($this->getRequest(), 20, 10)
        );

        $this->getView()->countryList = $list->findList();

        return $this->getView();
    }
}