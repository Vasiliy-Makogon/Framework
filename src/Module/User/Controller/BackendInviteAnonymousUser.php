<?php

class Krugozor_Module_User_Controller_BackendInviteAnonymousUser extends Krugozor_Controller_Ajax
{
    public function run()
    {
        $this->getView('Ajax');
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        $data = ['message' => ''];

        try {
            if (!$this->checkAccess()) {
                throw new Exception(strip_tags($this->getView()->getLang()['notification']['message']['forbidden_access']));
            }

            $id = $this->getRequest()->getRequest('advert');

            if (!$id) {
                throw new Exception(strip_tags($this->getView()->getLang()['notification']['message']['id_element_not_exists']));
            }

            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                throw new Exception(strip_tags($this->getView()->getLang()['notification']['message']['bad_id_element']));
            }

            $advert = $this->getMapper('Advert/Advert')->findModelById($id);

            if (!$advert->getId()) {
                throw new Exception(strip_tags($this->getView()->getLang()['notification']['message']['element_does_not_exist']));
            } else if (!$advert->getEmail()->getValue()) {
                throw new Exception('В объявлении не указан email-адрес');
            }

            $sendmail = new Krugozor_Mail();
            $sendmail->setTo($advert->getEmail()->getValue());
            $sendmail->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
            $sendmail->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
            $sendmail->setHeader('Приглашение на сайт ' . Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE']);
            $sendmail->setLang(Krugozor_Registry::getInstance()->LOCALIZATION['LANG']);
            $sendmail->setTemplate($this->getRealLocalTemplatePath('BackendInviteAnonymousUser'));
            $sendmail->advert = $advert;
            $sendmail->host = Krugozor_Registry::getInstance()->HOSTINFO['HOST'];
            $sendmail->host_simple = Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE'];
            $sendmail->host_url = Krugozor_Registry::getInstance()->HOSTINFO['HOST_URL'];
            $sendmail->send();

            $data['message'] = $sendmail->getMessage();

            $model = $this->getMapper('User/InviteAnonymousUser')->createModel();
            $model->setUniqueCookieId($advert->getUniqueUserCookieId());
            $this->getMapper('User/InviteAnonymousUser')->insert($model);

        } catch (Exception $e) {
            $data['message'] = $e->getMessage();
        }

        $this->getView()->getStorage()->clear()->setData($data);

        return $this->getView();
    }
}
