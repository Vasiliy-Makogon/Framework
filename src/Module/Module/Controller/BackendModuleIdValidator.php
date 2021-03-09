<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Module\Module\Model\Module;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

trait BackendModuleIdValidator
{
    /**
     * @var Module
     */
    protected $module;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                    ->setNotificationUrl('/module/backend-main/')
                    ->run();
            }

            $this->module = $this->getMapper('Module/Module')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->module->getId()) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                    ->setNotificationUrl('/module/backend-main/')
                    ->run();
            }
        }
    }
}