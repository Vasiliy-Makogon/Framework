<?php

namespace Krugozor\Framework\Module\Getpassword\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Mail;
use Krugozor\Framework\Module\Getpassword\Service\GetpasswordService;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Validator;

class FrontendGetpasswordEnd extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', 'Local/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        try {
            $service = new GetpasswordService($this->getMapper('User/User'));
            $service->setGetpasswordMapper($this->getMapper('Getpassword/Getpassword'));

            if ($service->isValidHash($this->getRequest()->getRequest('hash'))) {
                $mail = new Mail();
                $mail->setFrom(Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setReplyTo(Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setHeader($this->getView()->getLang()['mail']['header']['send_mail_user']);
                $mail->setTemplate($this->getRealLocalTemplatePath('FrontendGetpasswordSendPassword'));

                $service->setMail($mail)->sendMailWithNewPassword();

                return $this->createNotification()
                    ->setMessage($this->getView()->getLang()['notification']['message']['getpassword_send_message'])
                    ->setNotificationUrl('/authorization/frontend-login/')
                    ->run();
            } else {
                $notification = $this->createNotification()
                    ->setType(Notification::TYPE_WARNING)
                    ->setHeader($this->getView()->getLang()['notification']['header']['bad_hash_header'])
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_hash_message']);
                $this->getView()->setNotification($notification);
            }
        } catch (\Exception $e) {
            $validator = new Validator('common/general');
            $validator->addError('common_error', 'SYSTEM_ERROR', array('error_message' => $e->getMessage()));
            $this->getView()->err = $validator->getErrors();
            $this->log($e->getMessage());
        }

        return $this->getView();
    }
}