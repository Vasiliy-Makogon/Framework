<?php

namespace Krugozor\Framework\Module\Captcha\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Module\Captcha\Model\Captcha;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Session;

class Main extends Controller
{
    public function run()
    {
        $this->getResponse()->setHeader(Response::HEADER_CONTENT_TYPE, 'image/png');

        $session_name = $this->getRequest()->getRequest('session_name', 'string');
        $session_id   = $this->getRequest()->getRequest('session_id', 'string');

        if (empty($session_name)) {
            $this->log('Не указан session ID в ' . __FILE__);
            exit;
        }

        $session = Session::getInstance($session_name, $session_id);
        $captcha = new Captcha(Registry::getInstance()->PATH['CAPCHA_FONT']);
        $session->code = $captcha->getCode();
        $captcha->create();

        return $captcha;
    }
}