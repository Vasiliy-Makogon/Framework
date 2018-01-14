<?php
/**
 * На данный контроллер идет location, если необходима оплата активации объявления.
 */
class Krugozor_Module_Advert_Controller_Payment extends Krugozor_Module_Advert_Controller_FrontendCommon
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n('Common/FrontendGeneral');

        if ($result = $this->checkIdOnValid()) {
            return $result;
        }

        return $this->getView();
    }
}