<?php

namespace Krugozor\Framework\Module\Robokassa\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Robokassa\Service\Robokassa;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Registry;

/**
 * Информирование об успешной оплате.
 */
class Success extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        try {
            $robokassa = new Robokassa();
            $action = $this->getRequest()->getGet('SHP_ACTION', 'decimal');
            $id_advert = $this->getRequest()->getGet('SHP_ADVERT', 'decimal');

            if (!$robokassa->checkSuccess($this->getRequest())) {
                throw new \Exception($this->getView()->getLang()['bad_signature']);
            }

            if (!$id_advert) {
                throw new \Exception($this->getView()->getLang()['not_found_advert_id']);
            }

            $advert = $this->getMapper('Advert/Advert')->findModelById($id_advert);

            if (!$advert->getId()) {
                throw new \Exception($this->getView()->getLang()['not_found_advert']);
            }

            switch ($action) {
                case Robokassa::ACTION_ACTIVATE:
                    $message = $this->getView()->getLang()['advert_set_payment'];
                    break;

                case Robokassa::ACTION_TOP:
                    $message = $this->getView()->getLang()['advert_set_vip'];
                    break;

                case Robokassa::ACTION_SPECIAL:
                    $message = $this->getView()->getLang()['advert_set_vip'];
                    break;

                default:
                    throw new Exception($this->getView()->getLang()['undefined_action']);
            }

            $notification = $this->createNotification()
                ->setMessage($message)
                ->addParam('id', $advert->getId())
                ->addParam('advert_header', $advert->getHeader())
                ->addParam('advert_vip_date', $advert->getVipDate() ? $advert->getVipDate()->format('d.m.Y H:i') : null)
                ->addParam('http_host', Registry::getInstance()->HOSTINFO['HOST_SIMPLE']);
            $this->getView()->setNotification($notification);

        } catch (\Exception $e) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setHeader($this->getView()->getLang()['notification_header_fail'])
                ->setMessage($e->getMessage())
                ->addParam('id', $id_advert);
            $this->getView()->setNotification($notification);

            $this->log($e->getMessage());
        }

        return $this->getView();
    }
}