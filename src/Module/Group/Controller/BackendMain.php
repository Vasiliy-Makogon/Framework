<?php

class Krugozor_Module_Group_Controller_BackendMain extends Krugozor_Module_Group_Controller_BackendCommon
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Controller::run()
     */
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/BackendGeneral',
            'Group/BackendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/admin/')
                ->run();
        }

        $list = new Krugozor_Module_Group_Service_List(
            $this->getRequest(),
            $this->getMapper('Group/Group'),
            Krugozor_Pagination_Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->groupList = $list->findList();
        $this->getView()->uri = $this->getRequest()->getUri()->getEscapeUriValue();

        return $this->getView();
    }
}