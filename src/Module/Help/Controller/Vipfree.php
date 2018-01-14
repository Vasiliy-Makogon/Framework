<?php
class Krugozor_Module_Help_Controller_Vipfree extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/FrontendGeneral')->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        return $this->getView();
    }

    private function post()
    {
        $validator = new Krugozor_Validator('common/general');
        $url = $this->getRequest()->getPost()->url;
        $validator->add('url', new Krugozor_Validator_Empty($url));
        $validator->add('url', new Krugozor_Validator_Url($url));
        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setHeader($this->getView()->getLang()['notification']['action_failed'])
                ->setMessage($this->getView()->getLang()['notification']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            try {
                $sendmail = new Krugozor_Mail();
                $sendmail->setTo(Krugozor_Registry::getInstance()->EMAIL['ADMIN']);
                $sendmail->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                $sendmail->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                $sendmail->setHeader('Проверка ссылки');
                $sendmail->setLang(Krugozor_Registry::getInstance()->LOCALIZATION['LANG']);
                $sendmail->setTemplate($this->getRealLocalTemplatePath('CheckLink'));
                $sendmail->url = $this->getRequest()->getPost()->url;
                $sendmail->send();
            } catch (Exception $e) {
                $this->log($e->getMessage());
            }

            return $this->createNotification()
                ->setMessage('<p>Спасибо, в ближайшее время мы проверим вашу страницу и разместим ваше объявление в линейку VIP-объявлений.</p>')
                ->setNotificationUrl('/help/vipfree')
                ->run();
        }
    }
}