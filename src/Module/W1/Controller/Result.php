<?php
/**
 * Предоставление услуги по факту оплаты.
 */
class Krugozor_Module_W1_Controller_Result extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Robokassa/Success');

        try {
            $robokassa = new Krugozor_Module_Robokassa_Service_Robokassa();

            $action = $this->getRequest()->getGet('SHP_ACTION', 'decimal');
            $id_advert = $this->getRequest()->getGet('SHP_ADVERT', 'decimal');
            $inv_id = $this->getRequest()->getGet('inv_id', 'string');

            if (!$robokassa->checkResult($this->getRequest())) {
                throw new Exception($this->getView()->getLang()['bad_signature']);
            }

            if (!$id_advert) {
                throw new Exception($this->getView()->getLang()['not_found_advert_id']);
            }

            $advert = $this->getMapper('Advert/Advert')->findModelById($id_advert);

            if (!$advert->getId()) {
                throw new Exception($this->getView()->getLang()['not_found_advert']);
            }

            switch ($action) {
                case Krugozor_Module_Robokassa_Service_Robokassa::ACTION_ACTIVATE:
                    $advert->setPayment(1);
                    $this->getView()->result = "OK$inv_id";
                    break;

                case Krugozor_Module_Robokassa_Service_Robokassa::ACTION_TOP:
                    $advert->setVipStatus();
                    $this->getView()->result = "OK$inv_id";
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