<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\User\Model\City;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

abstract class BackendCityCommon extends Controller
{
    /**
     * @var City
     */
    protected $city;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                $message = $this->getView()->getLang()['notification']['message']['bad_id_element'];
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl('/user/backend-city-list/')
                    ->run();
            }

            $this->city = $this->getMapper('User/City')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->city->getId()) {
                $message = $this->getView()->getLang()['notification']['message']['element_does_not_exist'];
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl('/user/backend-city-list/')
                    ->run();
            }
        }
    }
}