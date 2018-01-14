<?php
class Krugozor_Module_Category_Controller_BackendMain extends Krugozor_Module_Category_Controller_BackendCommon
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

        $this->getView()->categories = $this->getMapper('Category/Category')->loadTree(
            array('order' => array('order' => 'DESC'))
        );

        return $this->getView();
    }
}