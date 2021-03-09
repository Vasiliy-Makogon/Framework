<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Module\Module\Model\Controller;
use Krugozor\Framework\Module\Module\Model\Module;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

trait BackendControllerIdValidator
{
    /**
     * @var Controller
     */
    protected $controller;

    /**
     * @var Module
     */
    protected $module;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                $message = $this->getView()->getLang()['notification']['message']['bad_id_element'];
                $url = '/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module');

                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl($url)
                    ->run();
            }

            $this->controller = $this->getMapper('Module/Controller')->findModelById($id);

            if (!$this->controller->getId()) {
                $message = $this->getView()->getLang()['notification']['message']['element_does_not_exist'];
                $url = '/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module');

                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($message)
                    ->setNotificationUrl($url)
                    ->run();
            }
        }
    }
}