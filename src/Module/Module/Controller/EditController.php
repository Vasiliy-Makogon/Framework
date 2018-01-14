<?php
class Krugozor_Module_Module_Controller_EditController extends Krugozor_Module_Module_Controller_CommonController
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

        if (Krugozor_Http_Request::isPost() && ($result = $this->post())) {
            return $result;
        }

        $this->getView()->modules = $this->getMapper('Module/Module')->findModelListByParams();
        $this->getView()->controller = $this->controller;

        return $this->getView();
    }

    protected function post()
    {
        $this->controller->setData($this->getRequest()->getPost('controller'));

        $validator = new Krugozor_Validator('common/general');
        $validator->addModelErrors($this->controller->getValidateErrors())
                  ->validate();

        $notification = $this->createNotification();

        if ($this->getView()->err = $validator->getErrors()) {
            $notification->setType(Krugozor_Notification::TYPE_ALERT)
                         ->setMessage($this->getView()->getLang()['notification']['post_errors']);
            $this->getView()->setNotification($notification);
        } else {
            $this->getMapper('Module/Controller')->saveModel($this->controller);

            $notification->setMessage($this->getView()->getLang()['notification']['message']['data_saved']);
            $notification->setNotificationUrl($this->getRequest()->getRequest('return_on_page')
                                                  ? '/module/edit-controller/?id=' . $this->controller->getId() . '&id_module=' . $this->controller->getIdModule()
                                                  : ($this->getRequest()->getRequest('referer')
                                                     ? $this->getRequest()->getRequest('referer')
                                                     : '/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module')
                                                    )
                                             );
            return $notification->run();
        }
    }
}