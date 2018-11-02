<?php

namespace Krugozor\Framework\Module\Getpassword\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Mail;
use Krugozor\Framework\Module\Getpassword\Service\GetpasswordService;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Validator;
use Krugozor\Framework\Validator\StringLength;
use Krugozor\Framework\Validator\CharPassword;
use Krugozor\Framework\Validator\Email;

class FrontendGetpassword extends Controller
{
    public function run()
    {
        if (!$this->getCurrentUser()->isGuest()) {
            return $this->getResponse()->setHeader(Response::HEADER_LOCATION, '/authorization/frontend-login/');
        }

        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        return $this->getView();
    }

    private function post()
    {
        $validator = new Validator('common/general', 'getpassword/getpassword');

        if ($user_login = $this->getRequest()->getPost('user')->login) {
            $validator->add('user_login', new StringLength($user_login));
            $validator->add('user_login', new CharPassword($user_login));
        } else if ($user_email = $this->getRequest()->getPost('user')->email) {
            $validator->add('user_email', new StringLength($user_email));
            $validator->add('user_email', new Email($user_email));
        } else {
            $validator->addError('common_error', 'NON_EXIST_REG_DATA');
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            if (!empty($user_login)) {
                $user = $this->getMapper('User/User')->findByLogin($user_login);
            } else if (!empty($user_email)) {
                $user = $this->getMapper('User/User')->findByEmail($user_email);
            }

            $notification = $this->createNotification();

            if (!$user->getId()) {
                $notification->setType(Notification::TYPE_ALERT);
                $notification->setMessage($this->getView()->getLang()['notification']['message']['user_not_exist']);
                $this->getView()->setNotification($notification);
            } elseif (!$user->getEmail()->getValue()) {
                $notification->setType(Notification::TYPE_WARNING);
                $notification->setMessage($this->getView()->getLang()['notification']['message']['user_mail_not_exist']);
                $this->getView()->setNotification($notification);
            } else {
                $mail = new Mail();
                $mail->setFrom(Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setReplyTo(Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setHeader($this->getView()->getLang()['mail']['header']['send_mail_user']);
                $mail->setTemplate($this->getRealLocalTemplatePath('FrontendGetpasswordSendTest'));

                try {
                    (new GetpasswordService($this->getMapper('User/User')))
                        ->setUser($user)
                        ->setMail($mail)
                        ->setGetpasswordMapper($this->getMapper('Getpassword/Getpassword'))
                        ->sendEmailWithHash();

                    return $notification
                        ->setMessage($this->getView()->getLang()['notification']['message']['test_send_ok'])
                        ->setNotificationUrl($this->getRequest()->getCanonicalRequestUri()->getSimpleUriValue())
                        ->run();
                } catch (\Exception $e) {
                    $validator->addError('common_error', 'SYSTEM_ERROR', ['error_message' => $e->getMessage()]);
                    $this->getView()->err = $validator->getErrors();

                    $this->log($e->getMessage());

                    $notification->setType(Notification::TYPE_ALERT);
                    $notification->setMessage($this->getView()->getLang()['notification']['message']['unknown_error']);
                    $this->getView()->setNotification($notification);
                }
            }
        }

        $this->getView()->user_login = $this->getRequest()->getPost('user')->login;
        $this->getView()->user_email = $this->getRequest()->getPost('user')->email;
    }
}