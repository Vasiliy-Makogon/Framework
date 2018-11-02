<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Notification;

class FrontendDeleteAdvert extends FrontendCommon
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
            $message = $this->getView()->getLang()['notification']['message']['forbidden_access'];
            $url = $this->getRequest()->getRequest('referrer') ?: '/authorization/frontend-login/';

            return $this->createNotification()
                ->setMessage($message)
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl($url)
                ->run();
        }

        $this->getMapper('Advert/Advert')->deleteById($this->advert);

        $message = $this->getView()->getLang()['notification']['message']['advert_delete'];
        $url = $this->getRequest()->getRequest('referrer') ?: '/advert/frontend-user-adverts-list/';

        return $this->createNotification()
            ->setMessage($message)
            ->addParam('advert_header', $this->advert->getHeader())
            ->setNotificationUrl($url)
            ->run();
    }
}