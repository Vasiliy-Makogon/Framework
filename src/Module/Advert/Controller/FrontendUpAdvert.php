<?php
class Krugozor_Module_Advert_Controller_FrontendUpAdvert extends Krugozor_Module_Advert_Controller_FrontendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/FrontendGeneral', 'Advert/FrontendCommon');

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        if (!$this->checkAccess() || $this->getCurrentUser()->getId() !== $this->advert->getIdUser()) {
            return $this->createNotification()
                        ->setMessage($this->getView()->getLang()['notification']['message']['forbidden_access'])
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setNotificationUrl($this->getRequest()->getRequest('referrer')
                                         ? $this->getRequest()->getRequest('referrer')
                                         : array('my'))
                        ->run();
        }

        $notification = $this->createNotification();
        $notification->addParam('advert_header', Krugozor_Helper_Format::hsc($this->advert->getHeader()));

        if ($this->getMapper('Advert/Advert')->updateDateCreate($this->advert)) {
            $notification->setMessage($this->getView()->getLang()['notification']['message']['advert_date_create_update']);
        } else {
            $notification->setType(Krugozor_Notification::TYPE_WARNING);
            $notification->addParam('date', $this->advert->getExpireRestrictionUpdateCreateDate()->i);
            $notification->setMessage($this->getView()->getLang()['notification']['message']['advert_date_create_not_update']);
        }

        $notification->setNotificationUrl($this->getRequest()->getRequest('referrer') ?: '/my/adverts/');

        return $notification->run();
    }
}