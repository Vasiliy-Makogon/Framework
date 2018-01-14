<?php

class Krugozor_Module_User_Controller_BackendEdit extends Krugozor_Module_User_Controller_BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            'User/BackendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/user/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->user)) {
            $this->user = $this->getMapper('User/User')->createModel();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
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

        $validator = new Krugozor_Validator('common/general', 'user/registration');

        $validator->addModelErrors($this->user->getValidateErrors());

        if ($this->user->getLogin()) {
            $validator->add('login', new Krugozor_Module_User_Validator_UserLoginExists(
                    $this->user, $this->getMapper('User/User'))
            );
        }

        if (!$this->user->getId()) {
            $validator->add('password_1', new Krugozor_Validator_Empty(
                    $this->getRequest()->getRequest()->user->password_1)
            );
            $validator->add('password_1', new Krugozor_Validator_CharPassword(
                    $this->getRequest()->getRequest()->user->password_1)
            );

            $validator->add('password_2', new Krugozor_Validator_Empty(
                    $this->getRequest()->getRequest()->user->password_2)
            );
            $validator->add('password_2', new Krugozor_Validator_CharPassword(
                    $this->getRequest()->getRequest()->user->password_2)
            );
        }

        if (!empty($this->getRequest()->getRequest()->user->password_1) &&
            !empty($this->getRequest()->getRequest()->user->password_2)
        ) {
            $validator->add('password',
                new Krugozor_Module_User_Validator_UserPasswordsCompare(
                    $this->getRequest()->getRequest()->user->password_1,
                    $this->getRequest()->getRequest()->user->password_2
                )
            );
        }

        if ($this->user->getEmail()->getValue()) {
            $validator->add('email', new Krugozor_Module_User_Validator_UserMailExists(
                $this->user, $this->getMapper('User/User')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['post_errors']);
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

            return $this->createNotification()
                ->setMessage($this->getRequest()->getRequest('return_on_page')
                    ? $this->getView()->getLang()['notification']['message']['user_edit_ok_no_link']
                    : $this->getView()->getLang()['notification']['message']['user_edit_ok'])
                ->addParam('user_name', $this->user->getFullName())
                ->addParam('id_user', $this->user->getId())
                ->setNotificationUrl($this->getRequest()->getRequest('return_on_page')
                    ? '/user/backend-edit/?id=' . $this->user->getId()
                    : ($this->getRequest()->getRequest('referer') ?: '/user/backend-main/')
                )
                ->run();
        }

        return false;
    }
}