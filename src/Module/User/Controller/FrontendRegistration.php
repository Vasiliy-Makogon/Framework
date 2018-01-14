<?php

class Krugozor_Module_User_Controller_FrontendRegistration extends Krugozor_Controller
{
    private $user;

    public function run()
    {
        if (!$this->getCurrentUser()->isGuest()) {
            return $this->createNotification()
                ->setHidden(true)
                ->setNotificationUrl('/my/')
                ->run();
        }

        $this->getView()->session_name = Krugozor_Session::getInstance('CAPTCHASID')->getName();
        $this->getView()->session_id = Krugozor_Session::getInstance()->getId();

        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->user = $this->getMapper('User/User')->createModel();

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->user = $this->user;
        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }

    private function post()
    {
        $this->user->setData($this->getRequest()->getPost('user')->getDataAsArray(), array('id', 'group'));

        $validator = new Krugozor_Validator('common/general', 'user/registration', 'captcha/common');
        $validator->addModelErrors($this->user->getValidateErrors());

        $validator->add('captcha', new Krugozor_Module_Captcha_Validator_Captcha(
            $this->getRequest()->getPost('captcha_code', 'decimal'), Krugozor_Session::getInstance()->code
        ));

        if ($this->user->getLogin()) {
            $validator->add('login', new Krugozor_Module_User_Validator_UserLoginExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->add('password_1', new Krugozor_Validator_IsNotEmpty(
            $this->getRequest()->getRequest('user')->password_1
        ));
        $validator->add('password_1', new Krugozor_Validator_CharPassword(
            $this->getRequest()->getRequest('user')->password_1
        ));

        if ($this->user->getEmail()->getValue()) {
            $validator->add('email', new Krugozor_Module_User_Validator_UserMailExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setHeader($this->getView()->getLang()['notification']['action_failed'])
                ->setMessage($this->getView()->getLang()['notification']['post_errors']);
            $this->getView()->setNotification($notification);

            $this->getView()->password_1 = $this->getRequest()->getRequest('user')->item('password_1');
        } else {
            $this->user->setUniqueCookieId($this->getCurrentUser()->getUniqueCookieId());
            $this->user->setPasswordAsMd5($this->getRequest()->getRequest('user')->password_1);
            $this->user->setIp($_SERVER['REMOTE_ADDR']);
            $this->user->setRegdate(new Krugozor_Type_Datetime());
            $this->getMapper('User/User')->saveModel($this->user);

            $this->getMapper('User/InviteAnonymousUser')->deleteByUniqueCookieId(
                $this->getCurrentUser()->getUniqueCookieId()
            );

            Krugozor_Session::getInstance()->destroy();

            try {
                if ($this->user->getEmail()->getValue()) {
                    $sendmail = new Krugozor_Mail();
                    $sendmail->setTo($this->user->getEmail()->getValue());
                    $sendmail->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                    $sendmail->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                    $sendmail->setHeader($this->getView()->getLang()['header']['send_mail_user']);
                    $sendmail->setLang(Krugozor_Registry::getInstance()->LOCALIZATION['LANG']);
                    $sendmail->setTemplate($this->getRealLocalTemplatePath('FrontendRegistrationSendData'));
                    $sendmail->user = $this->user;
                    $sendmail->user_password = $this->getRequest()->getRequest('user')->password_1;
                    $sendmail->host = Krugozor_Registry::getInstance()->HOSTINFO['HOST'];
                    $sendmail->host_simple = Krugozor_Registry::getInstance()->HOSTINFO['HOST_SIMPLE'];
                    $sendmail->send();
                }
            } catch (Exception $e) {
                $this->log($e->getMessage());
            }

            $auth = new Krugozor_Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
            $auth->processAuthorization(
                $this->user->getLogin(),
                $this->getRequest()->getRequest('user')->password_1,
                Krugozor_Authorization::AUTHORIZATION_ON_YEAR
            );

            return $this->createNotification()
                ->setHeader($this->getView()->getLang()['notification']['you_registration_ok'])
                ->setMessage($this->user->getEmail()->getValue()
                    ? $this->getView()->getLang()['notification']['you_registration_with_email']
                    : $this->getView()->getLang()['notification']['you_registration_without_email']
                )
                ->addParam('login', $this->user->getLogin())
                ->addParam('password', $this->getRequest()->getRequest('user')->password_1)
                ->setNotificationUrl('/my/adverts/edit/?from_registration=1')
                ->run();
        }
    }
}