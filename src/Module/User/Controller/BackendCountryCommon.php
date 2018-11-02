<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\User\Model\Country;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

abstract class BackendCountryCommon extends Controller
{
    /**
     * @var Country
     */
    protected $country;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                $message = $this->getView()->getLang()['notification']['message']['bad_id_element'];
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl('/user/backend-country-list/')
                    ->run();
            }

            $this->country = $this->getMapper('User/Country')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->country->getId()) {
                $message = $this->getView()->getLang()['notification']['message']['element_does_not_exist'];
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl('/user/backend-country-list/')
                    ->run();
            }
        }
    }
}