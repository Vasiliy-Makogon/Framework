<?php

namespace Krugozor\Framework\Module\Freekassa\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Registry;

/**
 * Информирование об успешной оплате. URL возврата.
 */
class Success extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $notification = $this->createNotification()
            ->setMessage($this->getView()->getLang()['advert_pay_success'])
            ->addParam('http_host', Registry::getInstance()->HOSTINFO['HOST_SIMPLE']);
        $this->getView()->setNotification($notification);

        return $this->getView();
    }
}