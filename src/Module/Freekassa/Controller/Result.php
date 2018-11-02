<?php

namespace Krugozor\Framework\Module\Freekassa\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Framework\Module\Freekassa\Service\Freekassa;
use Krugozor\Framework\Statical\Strings;

/**
 * Предоставление услуги по факту оплаты. Скрипт оповещения.
 */
class Result extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Freekassa/Success'
        );

        try {
            $freekassa = new Freekassa();

            $action = $this->getRequest()->getGet('us_ACTION', 'decimal');
            $id_advert = $this->getRequest()->getGet('us_ADVERT', 'decimal');

            if (!$freekassa->checkResult($this->getRequest())) {
                throw new \Exception($this->getView()->getLang()['bad_signature']);
            }

            if (!$id_advert) {
                throw new \Exception($this->getView()->getLang()['not_found_advert_id']);
            }

            /* @var $advert Advert */
            $advert = $this->getMapper('Advert/Advert')->findModelById($id_advert);

            if (!$advert->getId()) {
                $message = Strings::createMessageFromParams(
                    $this->getView()->getLang()['not_found_advert'],
                    ['id' => $id_advert]
                );
                throw new \Exception($message);
            }

            switch ($action) {
                case Freekassa::ACTION_ACTIVATE:
                    $advert->setPayment(1);
                    $this->getView()->result = "YES";
                    break;

                case Freekassa::ACTION_TOP:
                    $advert->setVipStatus();
                    $this->getView()->result = "YES";
                    break;

                case Freekassa::ACTION_SPECIAL:
                    $advert->setSpecialStatus();
                    $this->getView()->result = "YES";
                    break;

                default:
                    throw new \Exception($this->getView()->getLang()['undefined_action']);
            }

            $this->getMapper('Advert/Advert')->saveModel($advert);
        } catch (\Exception $e) {
            $this->log($e->getMessage());
            $this->getView()->result = $e->getMessage();
        }

        return $this->getView();
    }
}