<?php

class Krugozor_Module_User_Controller_BackendRegionEdit extends Krugozor_Module_User_Controller_BackendRegionCommon
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
                ->setNotificationUrl('/user/backend-region-list/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->region)) {
            $this->region = $this->getMapper('User/Region')->createModel();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->region = $this->region;
        $this->getView()->countryList = $this->getMapper('User/Country')->getListActiveCountry();

        return $this->getView();
    }

    protected function post()
    {
        if (!$this->getRequest()->getPost('region')->name_en) {
            $this->getRequest()->getPost('region')->name_en = $this->getRequest()->getPost('region')->name_ru;
        }

        $this->region->setData($this->getRequest()->getPost('region')->getDataAsArray());

        $validator = new Krugozor_Validator('common/general');
        $validator->addModelErrors($this->region->getValidateErrors());
        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('User/Region')->saveModel($this->region);

            return $notification->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl(
                    $this->getRequest()->getRequest('return_on_page')
                        ? '/user/backend-region-edit/?id=' . $this->region->getId()
                        : ($this->getRequest()->getRequest('referer') ?: '/user/backend-region-list/')
                )
                ->run();
        }

        return false;
    }
}