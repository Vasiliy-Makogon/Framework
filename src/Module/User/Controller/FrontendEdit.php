<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Authorization;
use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Module\User\Validator\UserLoginExists;
use Krugozor\Framework\Module\User\Validator\UserMailExists;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;
use Krugozor\Framework\Validator\CharPassword;

class FrontendEdit extends Controller
{
    /**
     * @var User
     */
    private $user;

    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', 'Local/FrontendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if ($this->getCurrentUser()->isGuest()) {
            return $this->createNotification()
                ->setHidden(true)
                ->setNotificationUrl('/authorization/frontend-login/')
                ->run();
        } else if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/authorization/frontend-login/')
                ->run();
        }


        $this->user = clone $this->getCurrentUser();

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->user = $this->user;
        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }

    protected function post()
    {
        $this->user->setData(
            $this->getRequest()->getPost('user'),
            array('id', 'group', 'active', 'unique_cookie_id')
        );

        $validator = new Validator('common/general', 'user/registration');
        $validator->addModelErrors($this->user->getValidateErrors());

        if ($this->user->getLogin()) {
            $validator->add('login', new UserLoginExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        if ($this->user->getLogin() !== $this->getCurrentUser()->getLogin()) {
            if (!$this->getRequest()->getRequest('user')->password_1) {
                $validator->addError('login', 'CHANGE_LOGIN_NEED_PASSWORD');
            } else if (md5($this->getRequest()->getRequest('user')->password_1) !== $this->user->getPassword()) {
                $validator->addError('password_1', 'CHANGE_LOGIN_WROND_PASSWORD');
            }
        }

        if ($this->getRequest()->getRequest('user')->password_1 != '') {
            $validator->add('password_1', new CharPassword(
                $this->getRequest()->getRequest('user')->password_1
            ));
        }

        if ($this->user->getEmail() && $this->user->getEmail()->getValue()) {
            $validator->add('email', new UserMailExists(
                    $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);

            $this->getView()->password_1 = $this->getRequest()->getRequest('user')->password_1;
        } else {
            // Если требуется изменить пароль, явно указываем его для объекта.
            if ($this->getRequest()->getRequest('user')->password_1) {
                $this->user->setPasswordAsMd5($this->getRequest()->getRequest('user')->password_1);
            }

            $this->getMapper('User/User')->saveModel($this->user);

            // Если поменяли пароль, то нужно сделать скрытую авторизацию.
            if ($this->getRequest()->getRequest('user')->password_1) {
                $auth = new Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
                $auth->processAuthorization(
                    $this->user->getLogin(),
                    $this->getRequest()->getRequest('user')->password_1,
                    Authorization::AUTHORIZATION_ON_YEAR
                );
            }

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl($this->getRequest()->getCanonicalRequestUri()->getSimpleUriValue())
                ->run();
        }
    }
}