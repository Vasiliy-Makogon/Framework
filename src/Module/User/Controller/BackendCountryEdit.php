<?php

class Krugozor_Module_User_Controller_BackendCountryEdit extends Krugozor_Module_User_Controller_BackendCountryCommon
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
                ->setNotificationUrl('/user/backend-country-list/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->country)) {
            $this->country = $this->getMapper('User/Country')->createModel();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->country = $this->country;

        return $this->getView();
    }

    protected function post()
    {
        if (!$this->getRequest()->getPost('country')->name_en) {
            $this->getRequest()->getPost('country')->name_en = $this->getRequest()->getPost('country')->name_ru;
        }

        $this->country->setData($this->getRequest()->getPost('country')->getDataAsArray());

        $validator = new Krugozor_Validator('common/general');
        $validator->addModelErrors($this->country->getValidateErrors());
        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('User/Country')->saveModel($this->country);

            return $notification->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl(
                    $this->getRequest()->getRequest('return_on_page')
                        ? '/user/backend-country-edit/?id=' . $this->country->getId()
                        : ($this->getRequest()->getRequest('referer') ?: '/user/backend-country-list/')
                )
                ->run();
        }

        return false;
    }
}