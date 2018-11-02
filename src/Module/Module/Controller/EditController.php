<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class EditController extends CommonController
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->controller)) {
            $this->controller = $this->getMapper('Module/Controller')
                ->createModel()
                ->setIdModule($this->getRequest()->getRequest('id_module'));
        }

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->modules = $this->getMapper('Module/Module')->findModelListByParams();
        $this->getView()->controller = $this->controller;

        return $this->getView();
    }

    protected function post()
    {
        $this->controller->setData($this->getRequest()->getPost('controller'));

        $validator = new Validator('Common/general');
        $validator
            ->addModelErrors($this->controller->getValidateErrors())
            ->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification
                ->setType(Notification::TYPE_ALERT)
                ->setMessage(
                    $this->getView()->getLang()['notification']['message']['post_errors']
                );
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('Module/Controller')->saveModel($this->controller);

            $message = $this->getView()->getLang()['notification']['message']['data_saved'];
            $url =
                $this->getRequest()->getRequest('return_on_page')
                    ? '/module/edit-controller/?id=' . $this->controller->getId() . '&id_module=' . $this->controller->getIdModule()
                    : ($this->getRequest()->getRequest('referer') ?: '/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module')
                );

            return $notification
                ->setMessage($message)
                ->setNotificationUrl($url)
                ->run();
        }
    }
}