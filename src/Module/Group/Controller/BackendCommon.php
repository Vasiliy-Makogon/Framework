<?php

namespace Krugozor\Framework\Module\Group\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Group\Model\Group;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

abstract class BackendCommon extends Controller
{
    /**
     * @var Group
     */
    protected $group;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_group'])
                    ->setNotificationUrl('/group/backend-main/')
                    ->run();
            }

            $this->group = $this->getMapper('Group/Group')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->group->getId()) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['group_does_not_exist'])
                    ->addParam('id', $this->getRequest()->getRequest('id'))
                    ->setNotificationUrl('/group/backend-main/')
                    ->run();
            }
        }
    }
}