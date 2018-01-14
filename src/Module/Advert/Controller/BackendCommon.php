<?php
abstract class Krugozor_Module_Advert_Controller_BackendCommon extends Krugozor_Controller
{
    /**
     * @var Krugozor_Module_Advert_Model_Advert
     */
    protected $advert;

    protected function checkIdOnValid()
    {
        if ($id = $this->getRequest()->getRequest('id')) {
            if (!Krugozor_Static_Numeric::is_decimal($id)) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_element'])
                            ->setNotificationUrl('/advert/backend-main/')
                            ->run();
            }

            $this->advert = $this->getMapper('Advert/Advert')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->advert->getId()) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                            ->setNotificationUrl('/advert/backend-main/')
                            ->run();
            }
        }
    }
}