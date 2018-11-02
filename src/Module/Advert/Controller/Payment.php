<?php

namespace Krugozor\Framework\Module\Advert\Controller;

/**
 * На данный контроллер идет location, если необходима оплата активации объявления.
 */
class Payment extends FrontendCommon
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