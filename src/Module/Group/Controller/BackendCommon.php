<?php

abstract class Krugozor_Module_Group_Controller_BackendCommon extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_Group_Model_Group
     */
    protected $group;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_group'])
                    ->setNotificationUrl('/group/backend-main/')
                    ->run();
            }

            $this->group = $this->getMapper('Group/Group')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->group->getId()) {
                return $this->createNotification()
                    ->setType(Krugozor_Notification::TYPE_ALERT)
                    ->setMessage($this->getView()->getLang()['notification']['message']['group_does_not_exist'])
                    ->addParam('id', $this->getRequest()->getRequest('id'))
                    ->setNotificationUrl('/group/backend-main/')
                    ->run();
            }
        }
    }
}