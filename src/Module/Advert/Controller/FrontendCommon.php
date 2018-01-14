<?php
abstract class Krugozor_Module_Advert_Controller_FrontendCommon extends Krugozor_Controller
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
                            ->setMessage($this->getView()->getLang()['notification']['message']['bad_id_advert'])
                            ->setNotificationUrl('/my/adverts/edit/')
                            ->run();
            }

            $this->advert = $this->getMapper('Advert/Advert')->findModelById(
                $this->getRequest()->getRequest('id')
            );

            if (!$this->advert->getId()) {
                return $this->createNotification()
                            ->setType(Krugozor_Notification::TYPE_ALERT)
                            ->setMessage($this->getView()->getLang()['notification']['message']['advert_does_not_exist'])
                            ->setNotificationUrl('/my/adverts/edit/')
                            ->run();
            }
        }

        return null;
    }
}