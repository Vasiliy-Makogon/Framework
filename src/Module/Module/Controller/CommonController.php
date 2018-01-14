<?php
abstract class Krugozor_Module_Module_Controller_CommonController extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_Module_Model_Controller
     */
    protected $controller;

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
                            ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                            ->run();
            }

            $this->controller = $this->getMapper('Module/Controller')->findModelById($id);

            if (!$this->controller->getId()) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                            ->setNotificationUrl('/module/edit-module/?id=' . $this->getRequest()->getRequest('id_module'))
                            ->run();
            }
        }
    }
}