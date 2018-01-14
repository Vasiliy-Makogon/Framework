<?php

abstract class Krugozor_Module_User_Controller_BackendCountryCommon extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_User_Model_Country
     */
    protected $country;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                    ->setNotificationUrl('/user/backend-country-list/')
                    ->run();
            }

            $this->country = $this->getMapper('User/Country')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->country->getId()) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                    ->addParam('id_user', $this->getRequest()->getRequest('id'))
                    ->setNotificationUrl('/user/backend-country-list/')
                    ->run();
            }
        }
    }
}