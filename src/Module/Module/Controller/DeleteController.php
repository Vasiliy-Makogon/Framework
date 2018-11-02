<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Notification;

class DeleteController extends CommonController
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->getRequest()->getRequest()->id) {
            return $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                ->run();
        }

        $this->getMapper('Module/Controller')->deleteById($this->controller);

        return $this->createNotification()
            ->setMessage($this->getView()->getLang()['notification']['message']['data_deleted'])
            ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
            ->run();
    }
}