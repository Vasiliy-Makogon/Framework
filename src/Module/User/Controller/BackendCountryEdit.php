<?php

namespace Krugozor\Framework\Module\User\Controller;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class BackendCountryEdit extends BackendCountryCommon
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
                ->setNotificationUrl('/user/backend-country-list/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->country)) {
            $this->country = $this->getMapper('User/Country')->createModel();
        }

        if (Request::isPost() && ($result = $this->post())) {
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

        $validator = new Validator('common/general');
        $validator->addModelErrors($this->country->getValidateErrors());
        $validator->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $message = $this->getView()->getLang()['notification']['message']['post_errors'];
            $notification
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($message);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('User/Country')->saveModel($this->country);

            $message = $this->getView()->getLang()['notification']['message']['data_saved'];
            $url = $this->getRequest()->getRequest('return_on_page')
                ? '/user/backend-country-edit/?id=' . $this->country->getId()
                : ($this->getRequest()->getRequest('referer') ?: '/user/backend-country-list/');

            return $notification->setMessage($message)
                ->setNotificationUrl($url)
                ->run($url);
        }

        return false;
    }
}