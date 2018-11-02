<?php

namespace Krugozor\Framework\Module\Category\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Category\Model\Category;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Statical\Numeric;

abstract class BackendCommon extends Controller
{
    /**
     * @var Category
     */
    protected $category;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Numeric::isDecimal($id)) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                    ->setNotificationUrl('/category/backend-main/')
                    ->run();
            }

            $this->category = $this->getMapper('Category/Category')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->category->getId()) {
                return $this->createNotification()
                    ->setType(Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                    ->setNotificationUrl('/category/backend-main/')
                    ->run();
            }
        }
    }
}