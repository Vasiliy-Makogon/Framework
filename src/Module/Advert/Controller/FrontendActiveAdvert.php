<?php
class Krugozor_Module_Advert_Controller_FrontendActiveAdvert extends Krugozor_Module_Advert_Controller_FrontendCommon
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
                        ->setNotificationUrl($this->getRequest()->getRequest('referrer') ?: '/my/')
                        ->run();
        }

        $this->getMapper('Advert/Advert')->saveModel($this->advert->invertActive());

        return $this->createNotification()
                    ->addParam('advert_header', $this->advert->getHeader())
                    ->setMessage($this->getView()->getLang()['notification']['message']['advert_active_' . (string) $this->advert->getActive()])
                    ->setNotificationUrl($this->getRequest()->getRequest('referrer') ?: '/my/adverts/')
                    ->run();
    }
}