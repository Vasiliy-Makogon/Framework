<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Notification;

/**
 * Операции над множеством объявлений на основе их идентификаторов.
 */
class BackendSetActions extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral'
        );

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/advert/backend-main/')
                ->run();
        }

        if (!$this->getRequest()->getRequest('ids')) {
            return $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['ids_elements_not_exists'])
                ->setNotificationUrl('/advert/backend-main/')
                ->run();
        }

        $adverts = $this->getMapper('Advert/Advert')->findModelListByIds(
            $this->getRequest()->getRequest('ids')->getData()
        );

        if (!$adverts->count()) {
            return $this->createNotification()
                ->setType(Notification::TYPE_WARNING)
                ->setMessage($this->getView()->getLang()['notification']['message']['elements_does_not_exists'])
                ->setNotificationUrl('/advert/backend-main/')
                ->run();
        }

        if ($this->getRequest()->getRequest('delete', 'string')) {
            foreach ($adverts as $advert) {
                if ($advert->getVipDate() !== null || $advert->getSpecialDate() !== null) {
                    continue;
                }

                $this->getMapper('Advert/Advert')->deleteById($advert);
            }

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_deleted'])
                ->setNotificationUrl($this->getRequest()->getRequest('referer'))
                ->run();
        }

        if ($this->getRequest()->getRequest('payment', 'string')) {
            foreach ($adverts as $advert) {
                $advert->setPayment(1);
                $this->getMapper('Advert/Advert')->saveModel($advert);
            }

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl($this->getRequest()->getRequest('referer'))
                ->run();
        }

        if ($this->getRequest()->getPost('change_advert_category', 'string') &&
            $id_category = $this->getRequest()->getRequest('category', 'decimal')) {
            $category = $this->getMapper('Category/Category')->findModelById($id_category);

            if ($category->getId()) {
                foreach ($adverts as $advert) {
                    $advert->setCategory($category->getId());
                    $advert->setEditDate('now');
                    $this->getMapper('Advert/Advert')->saveModel($advert);
                }
            }

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl($this->getRequest()->getRequest('referer'))
                ->run();
        }
    }
}