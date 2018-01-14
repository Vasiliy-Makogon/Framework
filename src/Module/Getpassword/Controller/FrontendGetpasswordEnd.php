<?php
class Krugozor_Module_Getpassword_Controller_FrontendGetpasswordEnd extends Krugozor_Controller
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Controller::run()
     */
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $this->getView()->current_user = $this->getCurrentUser();

        try {
            $service = new Krugozor_Module_Getpassword_Service_Getpassword($this->getMapper('User/User'));
            $service->setGetpasswordMapper($this->getMapper('Getpassword/Getpassword'));

            if ($service->isValidHash($this->getRequest()->getRequest('hash'))) {
                $mail = new Krugozor_Mail();
                $mail->setFrom(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setReplyTo(Krugozor_Registry::getInstance()->EMAIL['NOREPLY']);
                $mail->setHeader($this->getView()->getLang()['mail']['header']['send_mail_user']);
                $mail->setTemplate($this->getRealLocalTemplatePath('FrontendGetpasswordSendPassword'));

                $service->setMail($mail)->sendMailWithNewPassword();

                return $this->createNotification()
                            ->setMessage($this->getView()->getLang()['notification']['message']['getpassword_send_message'])
                            ->setNotificationUrl('/login/')
                            ->run();
            } else {
                $notification = $this->createNotification()
                                     ->setType(Krugozor_Notification::TYPE_WARNING)
                                     ->setHeader($this->getView()->getLang()['notification']['header']['bad_hash_header'])
                                     ->setMessage($this->getView()->getLang()['notification']['message']['bad_hash_message']);
                $this->getView()->setNotification($notification);
            }
        } catch (Exception $e) {
            $validator = new Krugozor_Validator('common/general');
            $validator->addError('common_error', 'SYSTEM_ERROR', array('error_message' => $e->getMessage()));
            $this->getView()->err = $validator->getErrors();
            $this->log($e->getMessage());
        }

        return $this->getView();
    }
}