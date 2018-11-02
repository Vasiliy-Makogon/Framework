<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

abstract class BackendCommon extends Controller
{
    /**
     * @var Advert
     */
    protected $advert;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                    ->setNotificationUrl('/advert/backend-main/')
                    ->run();
            }

            $this->advert = $this->getMapper('Advert/Advert')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->advert->getId()) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                    ->setNotificationUrl('/advert/backend-main/')
                    ->run();
            }
        }
    }
}