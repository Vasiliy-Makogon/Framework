<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class BackendCityEdit extends BackendCityCommon
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
                ->setNotificationUrl('/user/backend-city-list/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->city)) {
            $this->city = $this->getMapper('User/City')->createModel();
        }

        if (Request::isPost() && ($result = $this->post())) {
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

        $validator = new Validator('common/general');
        $validator->addModelErrors($this->city->getValidateErrors());
        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $message = $this->getView()->getLang()['notification']['message']['post_errors'];
            $notification
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($message);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('User/City')->saveModel($this->city);

            $message = $this->getView()->getLang()['notification']['message']['data_saved'];
            $url = $this->getRequest()->getRequest('return_on_page')
                ? '/user/backend-city-edit/?id=' . $this->city->getId()
                : ($this->getRequest()->getRequest('referer') ?: '/user/backend-city-list/');

            return $notification->setMessage($message)
                ->setNotificationUrl($url)
                ->run();
        }

        return false;
    }
}