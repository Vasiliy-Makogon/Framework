<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Notification;

class DeleteModule extends CommonModule
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/module/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->getRequest()->getRequest()->id) {
            return $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                ->setNotificationUrl('/module/backend-main/')
                ->run();
        }

        $this->getMapper('Module/Module')->delete($this->module);

        return $this->createNotification()
            ->setMessage($this->getView()->getLang()['notification']['message']['data_deleted'])
            ->setNotificationUrl($this->getRequest()->getRequest()->referer)
            ->run();
    }
}