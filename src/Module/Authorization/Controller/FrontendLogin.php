<?php
/**
 * Авторизация пользователя / личный кабинет.
 */
class Krugozor_Module_Authorization_Controller_FrontendLogin extends Krugozor_Controller
{
    /**
     * На сколько запоминать пароль, дней.
     *
     * @var int
     */
    const DEFAULT_AUTOLOGIN_DAYS = 365;

    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $user = $this->getMapper('User/User')->createModel();

        if (Krugozor_Http_Request::isPost()) {
            $user->setData($this->getRequest()->getRequest('user')->getDataAsArray());

            $validator = new Krugozor_Validator('common/general', 'authorization/login');
            $validator->addModelErrors($user->getValidateErrors())
                      ->add('password', new Krugozor_Validator_IsNotEmpty($user->getPassword()))
                      ->validate();

            if (!($this->getView()->err = $validator->getErrors())) {
                $days = !empty($this->getRequest()->getPost('autologin', 'decimal'))
                        ? $this->getRequest()->getPost('ml_autologin', 'decimal')
                        : 0;

                $auth = new Krugozor_Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
                if ($auth->processAuthorization($user->getLogin(), $user->getPassword(), $days)) {
                    return $this->createNotification()
                                ->setMessage($this->getView()->getLang()['notification']['message']['inside_system'])
                                ->setNotificationUrl('/my/')
                                ->run();
                } else {
                    $validator->addError('authorization', 'INCORRECT_AUTH_DATA');
                    $this->getView()->err = $validator->getErrors();

                    $notification = $this->createNotification()
                                         ->setType(Krugozor_Notification::TYPE_ALERT)
                                         ->setHeader($this->getView()->getLang()['notification']['header']['action_failed'])
                                         ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
                    $this->getView()->setNotification($notification);
                }
            }

            $this->getView()->autologin = $this->getRequest()->getRequest('autologin');
            $this->getView()->ml_autologin = $this->getRequest()->getRequest('ml_autologin');
        } else {
            $this->getView()->autologin = 0;
            $this->getView()->ml_autologin = self::DEFAULT_AUTOLOGIN_DAYS;
        }

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->referer = $this->getRequest()->getRequest('referer');
        $this->getView()->user = $user;

        return $this->getView();
    }
}