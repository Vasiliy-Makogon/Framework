<?php

class Krugozor_Module_User_Controller_BackendDelete extends Krugozor_Module_User_Controller_BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral', 'User/BackendCommon');

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/user/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->getRequest()->getRequest()->id)) {
            return $this->createNotification()
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['id_user_not_exists'])
                ->setNotificationUrl('/user/backend-main/')
                ->run();
        }

        $this->getMapper('User/User')->deleteById($this->user);

        return $this->createNotification()
            ->setType(Krugozor_Notification::TYPE_NORMAL)
            ->setMessage($this->getView()->getLang()['notification']['message']['user_delete'])
            ->addParam('user_name', Krugozor_Helper_Format::hsc($this->user->getFullName()))
            ->setNotificationUrl($this->getRequest()->getRequest('referer'))
            ->run();
    }
}