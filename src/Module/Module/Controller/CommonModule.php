<?php
abstract class Krugozor_Module_Module_Controller_CommonModule extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_Module_Model_Module
     */
    protected $module;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                            ->setNotificationUrl('/module/backend-main/')
                            ->run();
            }

            $this->module = $this->getMapper('Module/Module')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->module->getId()) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                            ->setNotificationUrl('/module/backend-main/')
                            ->run();
            }
        }
    }
}