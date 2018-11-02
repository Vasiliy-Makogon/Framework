<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

abstract class BackendCommon extends Controller
{
    /**
     * @var User
     */
    protected $user;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                $message = $this->getView()->getLang()['notification']['message']['bad_id_user'];
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl('/user/backend-main/')
                    ->run();
            }

            $this->user = $this->getMapper('User/User')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->user->getId()) {
                $message = $this->getView()->getLang()['notification']['message']['user_does_not_exist'];
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->addParam('id_user', $this->getRequest()->getRequest('id'))
                    ->setNotificationUrl('/user/backend-main/')
                    ->run();
            }
        }
    }
}