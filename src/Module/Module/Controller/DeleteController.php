<?php
class Krugozor_Module_Module_Controller_DeleteController extends Krugozor_Module_Module_Controller_CommonController
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

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

        if (!$this->getRequest()->getRequest()->id) {
            return $this->createNotification()
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                        ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                        ->run();
        }

        $this->getMapper('Module/Controller')->deleteById($this->controller);

        return $this->createNotification()
                    ->setMessage($this->getView()->getLang()['notification']['message']['data_deleted'])
                    ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                    ->run();
    }
}