<?php
/**
 * Выход из системы.
 */
class Krugozor_Module_Authorization_Controller_Logout extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        if (!$this->getCurrentUser()->isGuest()) {
            $auth = new Krugozor_Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
            $auth->logout();
        }

        return $this->createNotification()
                    ->setMessage($this->getView()->getLang()['notification']['message']['outside_system'])
                    ->setNotificationUrl($this->getRequest()->getRequest('referer') ?: '/')
                    ->run();
    }
}