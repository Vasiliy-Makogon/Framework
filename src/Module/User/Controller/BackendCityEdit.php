<?php

class Krugozor_Module_User_Controller_BackendCityEdit extends Krugozor_Module_User_Controller_BackendCityCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/user/backend-city-list/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->city)) {
            $this->city = $this->getMapper('User/City')->createModel();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->city = $this->city;

        return $this->getView();
    }

    protected function post()
    {
        if (!$this->getRequest()->getPost('city')->name_en) {
            $this->getRequest()->getPost('city')->name_en = $this->getRequest()->getPost('city')->name_ru;
        }

        $this->city->setData($this->getRequest()->getPost('city')->getDataAsArray());

        $validator = new Krugozor_Validator('common/general');
        $validator->addModelErrors($this->city->getValidateErrors());
        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('User/City')->saveModel($this->city);

            return $notification->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl(
                    $this->getRequest()->getRequest('return_on_page')
                        ? '/user/backend-city-edit/?id=' . $this->city->getId()
                        : ($this->getRequest()->getRequest('referer') ?: '/user/backend-city-list/')
                )
                ->run();
        }

        return false;
    }
}