<?php

class Krugozor_Module_User_Controller_FrontendEdit extends Krugozor_Controller
{
    private $user;

    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if ($this->getCurrentUser()->isGuest()) {
            return $this->createNotification()
                ->setHidden(true)
                ->setNotificationUrl('/my/')
                ->run();
        } else if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/my/')
                ->run();
        }

        $this->getView()->current_user = $this->user = $this->getCurrentUser();

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->user = $this->user;

        return $this->getView();
    }

    protected function post()
    {
        $this->user->setData($this->getRequest()->getPost('user')->getDataAsArray(), array('id', 'group', 'active', 'unique_cookie_id'));

        $validator = new Krugozor_Validator('common/general', 'user/registration');
        $validator->addModelErrors($this->user->getValidateErrors());

        if ($this->user->getLogin()) {
            $validator->add('login', new Krugozor_Module_User_Validator_UserLoginExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        if ($this->user->getLogin() !== $this->getCurrentUser()->getLogin() &&
            $this->getRequest()->getRequest('user')->password_1 == ''
        ) {
            $validator->addError('login', 'CHANGE_LOGIN_WITH_PASSWORD');
        }

        if ($this->getRequest()->getRequest('user')->password_1 != '') {
            $validator->add('password_1', new Krugozor_Validator_CharPassword
                (
                    $this->getRequest()->getRequest('user')->password_1)
            );
        }

        if ($this->user->getEmail() && $this->user->getEmail()->getValue()) {
            $validator->add('email', new Krugozor_Module_User_Validator_UserMailExists
                (
                    $this->user, $this->getMapper('User/User'))
            );
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $this->getView()->setNotification
            (
                $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['post_errors'])
            );

            $this->getView()->password_1 = $this->getRequest()->getRequest('user')->password_1;
        } else {
            // Если требуется изменить пароль, явно указываем его для объекта.
            if ($this->getRequest()->getRequest('user')->password_1) {
                $this->user->setPasswordAsMd5($this->getRequest()->getRequest('user')->password_1);
            }

            $this->getMapper('User/User')->saveModel($this->user);

            // Если поменяли пароль, то нужно сделать скрытую авторизацию.
            if ($this->getRequest()->getRequest('user')->password_1) {
                $auth = new Krugozor_Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
                $auth->processAuthorization(
                    $this->user->getLogin(),
                    $this->getRequest()->getRequest('user')->password_1,
                    Krugozor_Authorization::AUTHORIZATION_ON_YEAR
                );
            }

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl($this->getRequest()->getUri()->getSimpleUriValue())
                ->run();
        }
    }
}