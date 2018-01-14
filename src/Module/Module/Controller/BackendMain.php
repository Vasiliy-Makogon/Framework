<?php
class Krugozor_Module_Module_Controller_BackendMain extends Krugozor_Controller
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
                        ->setNotificationUrl('/admin/')
                        ->run();
        }

        $list = new Krugozor_Module_Module_Service_List(
            $this->getRequest(),
            $this->getMapper('Module/Module'),
            Krugozor_Pagination_Adapter::getManager($this->getRequest(), 15, 10)
        );

        $this->getView()->modulesList = $list->findList();

        return $this->getView();
    }
}