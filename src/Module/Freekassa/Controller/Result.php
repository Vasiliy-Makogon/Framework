<?php
/**
 * Предоставление услуги по факту оплаты. Скрипт оповещения.
 */
class Krugozor_Module_Freekassa_Controller_Result extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Freekassa/Success');

        try {
            $freekassa = new Krugozor_Module_Freekassa_Service_Freekassa();

            $action = $this->getRequest()->getGet('us_ACTION', 'decimal');
            $id_advert = $this->getRequest()->getGet('us_ADVERT', 'decimal');

            if (!$freekassa->checkResult($this->getRequest())) {
                throw new Exception($this->getView()->getLang()['bad_signature']);
            }

            if (!$id_advert) {
                throw new Exception($this->getView()->getLang()['not_found_advert_id']);
            }

            $advert = $this->getMapper('Advert/Advert')->findModelById($id_advert);

            if (!$advert->getId()) {
                $message = Krugozor_Static_String::createMessageFromParams($this->getView()->getLang()['not_found_advert'], ['id' => $id_advert]);
                throw new Exception($message);
            }

            switch ($action) {
                case Krugozor_Module_Freekassa_Service_Freekassa::ACTION_ACTIVATE:
                    $advert->setPayment(1);
                    $this->getView()->result = "YES";
                    break;

                case Krugozor_Module_Freekassa_Service_Freekassa::ACTION_TOP:
                    $advert->setVipStatus();
                    $this->getView()->result = "YES";
                    break;

                case Krugozor_Module_Freekassa_Service_Freekassa::ACTION_SPECIAL:
                    $advert->setSpecialStatus();
                    $this->getView()->result = "YES";
                    break;

                default:
                    throw new Exception($this->getView()->getLang()['undefined_action']);
            }

            $this->getMapper('Advert/Advert')->saveModel($advert);
        } catch (Exception $e) {
            $this->log($e->getMessage());
            $this->getView()->result = $e->getMessage();
        }

        return $this->getView();
    }
}