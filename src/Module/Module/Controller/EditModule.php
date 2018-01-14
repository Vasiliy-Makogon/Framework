<?php
class Krugozor_Module_Module_Controller_EditModule extends Krugozor_Module_Module_Controller_CommonModule
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Controller::run()
     */
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
                        ->setNotificationUrl('/module/backend-main/')
                        ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (empty($this->module)) {
            $this->module = $this->getMapper('Module/Module')->createModel();
        }

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->module = $this->module;

        return $this->getView();
    }

    protected function post()
    {
        $this->module->setData($this->getRequest()->getPost('module'));

        $validator = new Krugozor_Validator('common/general', 'module/editModule');
        $validator->addModelErrors($this->module->getValidateErrors());

        if ($this->module->getName()) {
            $validator->add('name', new Krugozor_Module_Module_Validator_ModuleNameExists(
                $this->module, $this->getMapper('Module/Module')
            ));
        }

        if ($this->module->getKey()) {
            $validator->add('key', new Krugozor_Module_Module_Validator_ModuleKeyExists(
                $this->module, $this->getMapper('Module/Module')
            ));
        }

        $validator->validate();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification = $this->createNotification()
                                 ->setType(Krugozor_Notification::TYPE_ALERT)
                                 ->setMessage($this->getView()->getLang()['notification']['message']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('Module/Module')->saveModel($this->module);

            return $this->createNotification()
                        ->setMessage($this->getView()->getLang()['notification']['message']['data_saved'])
                        ->setNotificationUrl($this->getRequest()->getRequest('return_on_page')
                                             ? '/module/edit-module/?id='.$this->module->id
                                             : ($this->getRequest()->getRequest()->referer
                                                ? $this->getRequest()->getRequest()->referer
                                                : '/module/backend-main/')
                                            )
                        ->run();
        }
    }
}