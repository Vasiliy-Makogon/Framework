<?php

class Krugozor_Module_User_Controller_BackendCityList extends Krugozor_Module_User_Controller_BackendCityCommon
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
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new Krugozor_Module_User_Service_CityList(
            $this->getRequest(),
            $this->getMapper('User/City'),
            Krugozor_Pagination_Adapter::getManager($this->getRequest(), 20, 10)
        );

        $this->getView()->cityList = $list->findList();
        $this->getView()->regionList = $this->getMapper('User/Region')->getListActiveRegion();

        return $this->getView();
    }
}