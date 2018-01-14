<?php

abstract class Krugozor_Module_User_Controller_BackendCityCommon extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_User_Model_City
     */
    protected $city;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                    ->setNotificationUrl('/user/backend-city-list/')
                    ->run();
            }

            $this->city = $this->getMapper('User/City')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->city->getId()) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                    ->addParam('id_user', $this->getRequest()->getRequest('id'))
                    ->setNotificationUrl('/user/backend-city-list/')
                    ->run();
            }
        }
    }
}