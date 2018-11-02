<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Notification;
use Krugozor\Framework\Pagination\Adapter;

class FrontendUserAdvertsList extends FrontendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/authorization/frontend-login/')
                ->run();
        }

        $this->getMapper('Advert/Advert')->updateAdvertsByUniqueUserCookieId($this->getCurrentUser());

        $pagination = Adapter::getManager($this->getRequest(), 15, 10);

        $this->getView()->adverts = $this->getMapper('Advert/Advert')->findListForUser(
            $this->getCurrentUser()->getId(), $pagination->getStartLimit(), $pagination->getStopLimit()
        );

        $this->getView()->pagination = $pagination->setCount(
            $this->getMapper('Advert/Advert')->getFoundRows()
        );
        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }
}