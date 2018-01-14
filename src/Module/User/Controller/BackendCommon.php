<?php

abstract class Krugozor_Module_User_Controller_BackendCommon extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_User_Model_User
     */
    protected $user;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_user'])
                    ->setNotificationUrl('/user/backend-main/')
                    ->run();
            }

            $this->user = $this->getMapper('User/User')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->user->getId()) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['user_does_not_exist'])
                    ->addParam('id_user', $this->getRequest()->getRequest('id'))
                    ->setNotificationUrl('/user/backend-main/')
                    ->run();
            }
        }
    }
}