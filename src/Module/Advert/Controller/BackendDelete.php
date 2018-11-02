<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Notification;

class BackendDelete extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/advert/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->getRequest()->getRequest()->id) {
            return $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                ->setNotificationUrl('/advert/backend-main/')
                ->run();
        }

        $this->getMapper('Advert/Advert')->deleteById($this->advert);

        $message = $this->getView()->getLang()['notification']['message']['data_deleted'];
        $url = $this->getRequest()->getRequest('referer') ?: '/advert/backend-main/';

        return $this->createNotification()
            ->setMessage($message)
            ->setNotificationUrl($url)
            ->run();
    }
}