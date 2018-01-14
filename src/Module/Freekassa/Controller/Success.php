<?php
/**
 * Информирование об успешной оплате. URL возврата.
 */
class Krugozor_Module_Freekassa_Controller_Success extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $notification = $this->createNotification()
                             ->setMessage($this->getView()->getLang()['advert_pay_success'])
                             ->addParam('http_host', Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE']);
        $this->getView()->setNotification($notification);

        return $this->getView();
    }
}