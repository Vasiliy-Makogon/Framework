<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Controller\Ajax;
use Krugozor\Framework\Mail;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Statical\Numeric;

class BackendInviteAnonymousUser extends Ajax
{
    public function run()
    {
        $this->getView('Ajax');
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        $data = ['message' => ''];

        try {
            if (!$this->checkAccess()) {
                $message = strip_tags($this->getView()->getLang()['notification']['message']['forbidden_access']);
                throw new \Exception($message);
            }

            $id = $this->getRequest()->getRequest('advert');

            if (!$id) {
                $message = strip_tags($this->getView()->getLang()['notification']['message']['id_element_not_exists']);
                throw new \Exception($message);
            }

            if (!Numeric::isDecimal($id)) {
                $message = strip_tags($this->getView()->getLang()['notification']['message']['bad_id_element']);
                throw new \Exception($message);
            }

            $advert = $this->getMapper('Advert/Advert')->findModelById($id);

            if (!$advert->getId()) {
                $message = strip_tags($this->getView()->getLang()['notification']['message']['element_does_not_exist']);
                throw new \Exception($message);
            } else if (!$advert->getEmail()->getValue()) {
                throw new \Exception('В объявлении не указан email-адрес');
            }

            $sendmail = new Mail();
            $sendmail->setTo($advert->getEmail()->getValue());
            $sendmail->setFrom(Registry::getInstance()->EMAIL['NOREPLY']);
            $sendmail->setReplyTo(Registry::getInstance()->EMAIL['NOREPLY']);
            $sendmail->setHeader('Приглашение на сайт ' . Registry::getInstance()->HOSTINFO['HOST_SIMPLE']);
            $sendmail->setLang(Registry::getInstance()->LOCALIZATION['LANG']);
            $sendmail->setTemplate($this->getRealLocalTemplatePath('BackendInviteAnonymousUser'));
            $sendmail->advert = $advert;
            $sendmail->host = Registry::getInstance()->HOSTINFO['HOST'];
            $sendmail->host_simple = Registry::getInstance()->HOSTINFO['HOST_SIMPLE'];
            $sendmail->host_url = Registry::getInstance()->HOSTINFO['HOST_URL'];
            $sendmail->send();

            $data['message'] = $sendmail->getMessage();

            $model = $this->getMapper('User/InviteAnonymousUser')->createModel();
            $model->setUniqueCookieId($advert->getUniqueUserCookieId());
            $this->getMapper('User/InviteAnonymousUser')->insert($model);

        } catch (\Exception $e) {
            $data['message'] = $e->getMessage();
        }

        $this->getView()->getStorage()->clear()->setData($data);

        return $this->getView();
    }
}
