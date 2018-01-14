<?php
class Krugozor_Module_Captcha_Controller_Main extends Krugozor_Controller
{
    public function run()
    {
        $this->getResponse()->setHeader(Krugozor_Http_Response::HEADER_CONTENT_TYPE, 'image/png');

        $session_name = $this->getRequest()->getRequest('session_name', 'string');
        $session_id   = $this->getRequest()->getRequest('session_id', 'string');

        if (empty($session_name)) {
            $this->log('Не указан session ID в ' . __FILE__);
            exit;
        }

        $session = Krugozor_Session::getInstance($session_name, $session_id);

        $captcha = new Krugozor_Module_Captcha_Model_Captcha(Krugozor_Registry::getInstance()->PATH['CAPCHA_FONT']);

        $session->code = $captcha->getCode();
        $captcha->create();

        return $captcha;
    }
}