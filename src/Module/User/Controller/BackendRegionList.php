<?php

class Krugozor_Module_User_Controller_BackendRegionList extends Krugozor_Module_User_Controller_BackendRegionCommon
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

        $list = new Krugozor_Module_User_Service_RegionList(
            $this->getRequest(),
            $this->getMapper('User/Region'),
            Krugozor_Pagination_Adapter::getManager($this->getRequest(), 20, 10)
        );

        $this->getView()->regionList = $list->findList();
        $this->getView()->countryList = $this->getMapper('User/Country')->getListActiveCountry();

        return $this->getView();
    }
}