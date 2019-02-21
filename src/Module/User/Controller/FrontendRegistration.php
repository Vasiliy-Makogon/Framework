<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Authorization;
use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Mail;
use Krugozor\Framework\Module\Captcha\Validator\Captcha;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Module\User\Validator\UserLoginExists;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Session;
use Krugozor\Framework\Type\Datetime;
use Krugozor\Framework\Validator;
use Krugozor\Framework\Validator\IsNotEmptyString;
use Krugozor\Framework\Validator\CharPassword;
use Krugozor\Framework\Module\User\Validator\UserMailExists;

class FrontendRegistration extends Controller
{
    /**
     * @var User
     */
    private $user;

    public function run()
    {
        if (!$this->getCurrentUser()->isGuest()) {
            return $this->createNotification()
                ->setHidden(true)
                ->setNotificationUrl('/authorization/frontend-login/')
                ->run();
        }

        $this->getView()->session_name = Session::getInstance('CAPTCHASID')->getName();
        $this->getView()->session_id = Session::getInstance()->getId();

        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', 'Local/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->user = $this->getMapper('User/User')->createModel();

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->user = $this->user;
        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }

    private function post()
    {
        $this->user->setData($this->getRequest()->getPost('user'), ['id', 'group', 'unique_cookie_id']);

        $validator = new Validator('common/general', 'user/registration', 'captcha/common');
        $validator->addModelErrors($this->user->getValidateErrors());

        $validator->add('captcha', new Captcha(
            $this->getRequest()->getPost('captcha_code', 'decimal'),
            Session::getInstance()->code
        ));

        if ($this->user->getLogin()) {
            $validator->add('login', new UserLoginExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->add('password_1', new IsNotEmptyString(
            $this->getRequest()->getRequest('user')->password_1
        ));
        $validator->add('password_1', new CharPassword(
            $this->getRequest()->getRequest('user')->password_1
        ));

        if ($this->user->getEmail()->getValue()) {
            $validator->add('email', new UserMailExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setHeader($this->getView()->getLang()['notification']['action_failed'])
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);

            $this->getView()->password_1 = $this->getRequest()->getRequest('user')['password_1'];
        } else {
            $this->user->setUniqueCookieId($this->getCurrentUser()->getUniqueCookieId());
            $this->user->setPasswordAsMd5($this->getRequest()->getRequest('user')->password_1);
            $this->user->setIp($_SERVER['REMOTE_ADDR']);
            $this->user->setRegdate(new Datetime());
            $this->getMapper('User/User')->saveModel($this->user);

            $this->getMapper('User/InviteAnonymousUser')->deleteByUniqueCookieId(
                $this->getCurrentUser()->getUniqueCookieId()
            );

            $this->getMapper('Advert/Advert')->updateAdvertsByUniqueUserCookieId($this->user);

            Session::getInstance()->destroy();

            try {
                if ($this->user->getEmail()->getValue()) {
                    $sendmail = new Mail();
                    $sendmail->setTo($this->user->getEmail()->getValue());
                    $sendmail->setFrom(Registry::getInstance()->EMAIL['NOREPLY']);
                    $sendmail->setReplyTo(Registry::getInstance()->EMAIL['NOREPLY']);
                    $sendmail->setHeader($this->getView()->getLang()['mail']['header']['send_mail_user_header']);
                    $sendmail->setLang(Registry::getInstance()->LOCALIZATION['LANG']);
                    $sendmail->setTemplate($this->getRealLocalTemplatePath('FrontendRegistrationSendData'));
                    $sendmail->user = $this->user;
                    $sendmail->user_password = $this->getRequest()->getRequest('user')->password_1;
                    $sendmail->host = Registry::getInstance()->HOSTINFO['HOST'];
                    $sendmail->host_simple = Registry::getInstance()->HOSTINFO['HOST_SIMPLE'];
                    $sendmail->send();
                }
            } catch (\Exception $e) {
                $this->log($e->getMessage());
            }

            $auth = new Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
            $auth->processAuthorization(
                $this->user->getLogin(),
                $this->getRequest()->getRequest('user')->password_1,
                Authorization::AUTHORIZATION_ON_YEAR
            );

            $message = $this->user->getEmail()->getValue()
                ? $this->getView()->getLang()['notification']['message']['you_registration_with_email']
                : $this->getView()->getLang()['notification']['message']['you_registration_without_email'];

            return $this->createNotification()
                ->setHeader($this->getView()->getLang()['notification']['header']['you_registration_ok'])
                ->setMessage($message)
                ->addParam('login', $this->user->getLogin())
                ->addParam('password', $this->getRequest()->getRequest('user')->password_1)
                ->setNotificationUrl('/advert/frontend-edit-advert/?from_registration=1')
                ->run();
        }
    }
}