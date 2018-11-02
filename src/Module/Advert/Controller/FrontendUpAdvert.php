<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Notification;

class FrontendUpAdvert extends FrontendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', 'Advert/FrontendCommon'
        );

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->checkAccess() || $this->getCurrentUser()->getId() !== $this->advert->getIdUser()) {
            $url = $this->getRequest()->getRequest('referrer') ?: '/authorization/frontend-login/';

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl($url)
                ->run();
        }

        $notification = $this->createNotification();
        $notification->addParam('advert_header', $this->advert->getHeader());

        if ($this->getMapper('Advert/Advert')->updateDateCreate($this->advert)) {
            $notification->setMessage($this->getView()->getLang()['notification']['message']['advert_date_create_update']);
        } else {
            $notification->setType(Notification::TYPE_WARNING);
            $notification->addParam('date', $this->advert->getExpireRestrictionUpdateCreateDate()->i);
            $notification->setMessage($this->getView()->getLang()['notification']['message']['advert_date_create_not_update']);
        }

        $notification->setNotificationUrl($this->getRequest()->getRequest('referrer') ?: '/advert/frontend-user-adverts-list/');

        return $notification->run();
    }
}