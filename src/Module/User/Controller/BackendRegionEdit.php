<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class BackendRegionEdit extends BackendRegionCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            $message = $this->getView()->getLang()['notification']['message']['forbidden_access'];
            return $this->createNotification()
                ->setMessage($message)
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/user/backend-region-list/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->region)) {
            $this->region = $this->getMapper('User/Region')->createModel();
        }

        if (Request::isPost() && ($result = $this->post())) {
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

        $validator = new Validator('common/general');
        $validator->addModelErrors($this->region->getValidateErrors());
        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $message = $this->getView()->getLang()['notification']['message']['post_errors'];
            $notification
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($message);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('User/Region')->saveModel($this->region);

            $message = $this->getView()->getLang()['notification']['message']['data_saved'];
            $url = $this->getRequest()->getRequest('return_on_page')
                ? '/user/backend-region-edit/?id=' . $this->region->getId()
                : ($this->getRequest()->getRequest('referer') ?: '/user/backend-region-list/');

            return $notification->setMessage($message)
                ->setNotificationUrl($url)
                ->run();
        }

        return false;
    }
}