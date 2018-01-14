<?php

class Krugozor_Module_Group_Controller_BackendDelete extends Krugozor_Module_Group_Controller_BackendCommon
{
    /**
     * (non-PHPdoc)
     * @see Krugozor_Controller::run()
     */
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/BackendGeneral', 'Group/BackendCommon');

        if (!$this->checkAccess()) {
            return $this->createNotification()
                ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setNotificationUrl('/group/backend-main/')
                ->run();
        }

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->getRequest()->getRequest('id')) {
            return $this->createNotification()
                ->setType(Krugozor_Notification::TYPE_ALERT)
                ->setMessage($this->getView()->getLang()['notification']['message']['id_group_not_exists'])
                ->setNotificationUrl('/group/backend-main/')
                ->run();
        }

        $this->getMapper('Group/Group')->delete($this->group);

        return $this->createNotification()
            ->setMessage($this->getView()->getLang()['notification']['message']['group_delete'])
            ->addParam('group_name', $this->group->getName())
            ->setNotificationUrl($this->getRequest()->getRequest('referer'))
            ->run();
    }
}