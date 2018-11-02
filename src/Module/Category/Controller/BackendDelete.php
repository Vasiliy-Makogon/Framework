<?php

namespace Krugozor\Framework\Module\Category\Controller;

use Krugozor\Framework\Notification;

class BackendDelete extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral'
        );

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/category/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->getRequest()->getRequest()->id) {
            return $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                ->setNotificationUrl('/category/backend-main/')
                ->run();
        }

        $this->getMapper('Category/Category')->deleteById($this->category);

        $message = $this->getView()->getLang()['notification']['message']['data_deleted'];
        $url = $this->getRequest()->getRequest('referer') ?: '/category/backend-main/';

        return $this->createNotification()
            ->setMessage($message)
            ->setNotificationUrl($url)
            ->run();
    }
}