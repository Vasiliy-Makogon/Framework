<?php

namespace Krugozor\Framework\Module\Category\Controller;

use Krugozor\Framework\Notification;

class BackendMotion extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

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

        $notification = $this->createNotification();

        switch ($this->getRequest()->getRequest('tomotion')) {
            case 'up':
                $this->getMapper('Category/Category')->motionUp(
                    $this->category, array('pid', $this->getRequest()->getRequest()->pid)
                );
                $notification->setMessage($this->getView()->getLang()['notification']['message']['element_motion_up']);
                break;

            case 'down':
                $this->getMapper('Category/Category')->motionDown(
                    $this->category, array('pid', $this->getRequest()->getRequest()->pid)
                );
                $notification->setMessage($this->getView()->getLang()['notification']['message']['element_motion_down']);
                break;

            default:
                $notification->setType(Notification::TYPE_ALERT);
                $notification->setMessage($this->getView()->getLang()['notification']['message']['unknown_tomotion']);
                break;
        }

        return $notification
            ->setNotificationUrl('/category/backend-main/')
            ->run();
    }
}