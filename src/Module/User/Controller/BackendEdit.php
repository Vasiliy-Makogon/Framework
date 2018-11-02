<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Module\User\Validator\UserLoginExists;
use Krugozor\Framework\Module\User\Validator\UserMailExists;
use Krugozor\Framework\Module\User\Validator\UserPasswordsCompare;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class BackendEdit extends BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', 'User/BackendCommon', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            $message = $this->getView()->getLang()['notification']['message']['forbidden_access'];
            return $this->createNotification()
                ->setMessage($message)
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/user/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->user)) {
            $this->user = $this->getMapper('User/User')->createModel();
        }

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->user = $this->user;
        $this->getView()->groups = $this->getMapper('Group/Group')->findAllGroupsWithoutGuest();
        $this->getView()->return_on_page = $this->getRequest()->getRequest('return_on_page');

        return $this->getView();
    }

    protected function post()
    {
        $this->user->setData($this->getRequest()->getPost('user')->getDataAsArray());

        $validator = new Validator('common/general', 'user/registration');
        $validator->addModelErrors($this->user->getValidateErrors());

        if ($this->user->getLogin()) {
            $validator->add('login', new UserLoginExists(
                    $this->user, $this->getMapper('User/User'))
            );
        }

        if (!$this->user->getId()) {
            $validator->add('password_1', new Validator\IsNotEmptyString(
                    $this->getRequest()->getRequest()->user->password_1)
            );
            $validator->add('password_1', new Validator\CharPassword(
                    $this->getRequest()->getRequest()->user->password_1)
            );

            $validator->add('password_2', new Validator\IsNotEmptyString(
                    $this->getRequest()->getRequest()->user->password_2)
            );
            $validator->add('password_2', new Validator\CharPassword(
                    $this->getRequest()->getRequest()->user->password_2)
            );
        }

        if (!empty($this->getRequest()->getRequest()->user->password_1) &&
            !empty($this->getRequest()->getRequest()->user->password_2)
        ) {
            $validator->add('password',
                new UserPasswordsCompare(
                    $this->getRequest()->getRequest()->user->password_1,
                    $this->getRequest()->getRequest()->user->password_2
                )
            );
        }

        if ($this->user->getEmail()->getValue()) {
            $validator->add('email', new UserMailExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $message = $this->getView()->getLang()['notification']['message']['post_errors'];
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($message);
            $this->getView()->setNotification($notification);

            $this->getView()->password_1 = $this->getRequest()->getRequest('user')->password_1;
            $this->getView()->password_2 = $this->getRequest()->getRequest('user')->password_2;
        } else {
            if (!empty($this->getRequest()->getRequest('user')->password_1) &&
                !empty($this->getRequest()->getRequest('user')->password_2)
            ) {
                $this->user->setPasswordAsMd5($this->getRequest()->getRequest('user')->password_1);
            }

            $this->getMapper('User/User')->saveModel($this->user);

            $message = $this->getRequest()->getRequest('return_on_page')
                ? $this->getView()->getLang()['notification']['message']['user_edit_ok_no_link']
                : $this->getView()->getLang()['notification']['message']['user_edit_ok'];

            $url = $this->getRequest()->getRequest('return_on_page')
                ? '/user/backend-edit/?id=' . $this->user->getId()
                : ($this->getRequest()->getRequest('referer') ?: '/user/backend-main/');

            return $this->createNotification()
                ->setMessage($message)
                ->addParam('user_name', $this->user->getFullName())
                ->addParam('id_user', $this->user->getId())
                ->setNotificationUrl($url)
                ->run();
        }

        return false;
    }
}