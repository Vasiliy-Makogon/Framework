<?php
class Krugozor_Module_Getpassword_Controller_FrontendGetpassword extends Krugozor_Controller
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Controller::run()
     */
    public function run()
    {
        if (!$this->getCurrentUser()->isGuest()) {
            return $this->getResponse()->setHeader(Krugozor_Http_Response::HEADER_LOCATION, '/my/');
        }

        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        return $this->getView();
    }

    private function post()
    {
        $validator = new Krugozor_Validator('common/general', 'getpassword/getpassword');

        if ($user_login = $this->getRequest()->getPost('user')->login) {
            $validator->add('user_login', new Krugozor_Validator_StringLength($user_login));
            $validator->add('user_login', new Krugozor_Validator_CharPassword($user_login));
        } else if ($user_email = $this->getRequest()->getPost('user')->email) {
            $validator->add('user_email', new Krugozor_Validator_StringLength($user_email));
            $validator->add('user_email', new Krugozor_Validator_Email($user_email));
        } else {
            $validator->addError('common_error', 'NON_EXIST_REG_DATA');
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                                 ->setType(Krugozor_Notification::TYPE_ALERT)
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
                $notification->setType(Krugozor_Notification::TYPE_ALERT);
                $notification->setMessage($this->getView()->getLang()['notification']['message']['user_not_exist']);
                $this->getView()->setNotification($notification);
            } elseif (!$user->getEmail()->getValue()) {
                $notification->setType(Krugozor_Notification::TYPE_WARNING);
                $notification->setMessage($this->getView()->getLang()['notification']['message']['user_mail_not_exist']);
                $this->getView()->setNotification($notification);
            } else {
                $mail = new Krugozor_Mail();
                $mail->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setHeader($this->getView()->getLang()['mail']['header']['send_mail_user']);
                $mail->setTemplate($this->getRealLocalTemplatePath('FrontendGetpasswordSendTest'));

                try {
                    $service = new Krugozor_Module_Getpassword_Service_Getpassword($this->getMapper('User/User'));
                    $service->setUser($user)
                            ->setMail($mail)
                            ->setGetpasswordMapper($this->getMapper('Getpassword/Getpassword'))
                            ->sendEmailWithHash();

                    return $notification->setMessage($this->getView()->getLang()['notification']['message']['test_send_ok'])
                                        ->setNotificationUrl($this->getRequest()->getUri()->getSimpleUriValue())
                                        ->run();
                } catch (Exception $e) {
                    $validator->addError('common_error', 'SYSTEM_ERROR', array('error_message' => $e->getMessage()));
                    $this->getView()->err = $validator->getErrors();

                    $this->log($e->getMessage());

                    $notification->setType(Krugozor_Notification::TYPE_ALERT);
                    $notification->setMessage($this->getView()->getLang()['notification']['message']['unknown_error']);
                    $this->getView()->setNotification($notification);
                }
            }
        }

        $this->getView()->user_login = $this->getRequest()->getPost('user')->login;
        $this->getView()->user_email = $this->getRequest()->getPost('user')->email;
    }
}