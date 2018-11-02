<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Notification;

class BackendDelete extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', 'User/BackendCommon'
        );

        if (!$this->checkAccess()) {
            $message = $this->getView()->getLang()['notification']['message']['forbidden_access'];
            return $this->createNotification()
                ->setMessage($message)
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/user/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->getRequest()->getRequest()->id)) {
            $message = $this->getView()->getLang()['notification']['message']['id_user_not_exists'];
            return $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($message)
                ->setNotificationUrl('/user/backend-main/')
                ->run();
        }

        $adverts = $this->getMapper('Advert/Advert')->findModelListByParams(
            ['where' => ['advert_id_user = ?i' => [$this->user->getId()]]]
        );
        /* @var $advert Advert */
        foreach ($adverts as $advert) {
            $this->getMapper('Advert/Advert')->deleteById($advert);
        }

        $this->getMapper('User/User')->deleteById($this->user);

        $message = $this->getView()->getLang()['notification']['message']['user_delete'];
        return $this->createNotification()
            ->setType(Notification::TYPE_NORMAL)
            ->setMessage($message)
            ->addParam('user_name', $this->user->getFullName())
            ->setNotificationUrl($this->getRequest()->getRequest('referer'))
            ->run();
    }
}