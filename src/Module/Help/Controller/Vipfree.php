<?php

namespace Krugozor\Framework\Module\Help\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Mail;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Validator;
use Krugozor\Framework\Validator\IsNotEmptyString;
use Krugozor\Framework\Validator\Url;

class Vipfree extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral'
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        return $this->getView();
    }

    private function post()
    {
        $validator = new Validator('common/general');
        $url = $this->getRequest()->getPost()->url;
        $validator->add('url', new IsNotEmptyString($url));
        $validator->add('url', new Url($url));
        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setHeader($this->getView()->getLang()['notification']['header']['action_failed'])
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            try {
                $sendmail = new Mail();
                $sendmail->setTo(Registry::getInstance()->EMAIL['ADMIN']);
                $sendmail->setFrom(Registry::getInstance()->EMAIL['NOREPLY']);
                $sendmail->setReplyTo(Registry::getInstance()->EMAIL['NOREPLY']);
                $sendmail->setHeader('Проверка ссылки');
                $sendmail->setLang(Registry::getInstance()->LOCALIZATION['LANG']);
                $sendmail->setTemplate($this->getRealLocalTemplatePath('CheckLink'));
                $sendmail->url = $this->getRequest()->getPost()->url;
                $sendmail->send();
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }

            return $this->createNotification()
                ->setMessage('<p>Спасибо, в ближайшее время мы проверим вашу страницу и разместим ваше объявление в линейку VIP-объявлений.</p>')
                ->setNotificationUrl('/help/vipfree')
                ->run();
        }
    }
}