<?php
class Krugozor_Module_Category_Controller_BackendDelete extends Krugozor_Module_Category_Controller_BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral');

        if (!$this->checkAccess()) {
            return $this->createNotification()
                        ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setNotificationUrl('/category/backend-main/')
                        ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->getRequest()->getRequest()->id) {
            return $this->createNotification()
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setMessage($this->getView()->getLang()['notification']['message']['id_element_not_exists'])
                        ->setNotificationUrl('/category/backend-main/')
                        ->run();
        }

        $this->getMapper('Category/Category')->deleteById($this->category);

        return $this->createNotification()
                    ->setMessage($this->getView()->getLang()['notification']['message']['data_deleted'])
                    ->setNotificationUrl($this->getRequest()->getRequest('referer')
                                         ? $this->getRequest()->getRequest('referer')
                                         : '/category/backend-main/')
                    ->run();
    }
}