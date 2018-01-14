<?php

class Krugozor_Module_User_Controller_BackendMain extends Krugozor_Module_User_Controller_BackendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            'User/BackendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new Krugozor_Module_User_Service_List(
            $this->getRequest(),
            $this->getMapper('User/User'),
            Krugozor_Pagination_Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->usersList = $list->findList();

        $this->getView()->search = $this->getRequest()->getRequest('search');
        $this->getView()->col = $this->getRequest()->getRequest('col');
        $this->getView()->user_active = $this->getRequest()->getRequest('user_active');

        $this->getView()->user_country = $this->getRequest()->getRequest('user_country');
        $this->getView()->user_region = $this->getRequest()->getRequest('user_region');
        $this->getView()->user_city = $this->getRequest()->getRequest('user_city');

        return $this->getView();
    }
}