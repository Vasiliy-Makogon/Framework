<?php

namespace Krugozor\Framework\Module\Module\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Module\Module\Validator\ModuleKeyExists;
use Krugozor\Framework\Module\Module\Validator\ModuleNameExists;
use Krugozor\Framework\Notification;
use Krugozor\Framework\Validator;

class EditModule extends Controller
{
    use BackendModuleIdValidator;

    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Notification::TYPE_ALERT)
                ->setNotificationUrl('/module/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->module)) {
            $this->module = $this->getMapper('Module/Module')->createModel();
        }

        if (Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->module = $this->module;
        $this->getView()->current_user = $this->getCurrentUser();

        return $this->getView();
    }

    protected function post()
    {
        $this->module->setData($this->getRequest()->getPost('module'));

        $validator = new Validator('Common/general', 'Module/editModule');
        $validator->addModelErrors($this->module->getValidateErrors());

        if ($this->module->getName()) {
            $validator->add('name', new ModuleNameExists(
                $this->module, $this->getMapper('Module/Module')
            ));
        }

        if ($this->module->getKey()) {
            $validator->add('key', new ModuleKeyExists(
                $this->module, $this->getMapper('Module/Module')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                ->setType(Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('Module/Module')->saveModel($this->module);

            $notification_url =
                $this->getRequest()->getRequest('return_on_page')
                    ? '/module/edit-module/?id=' . $this->module->id
                    : $this->getRequest()->getRequest()->referer ?: '/module/backend-main/';

            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                ->setNotificationUrl($notification_url)
                ->run();
        }
    }
}