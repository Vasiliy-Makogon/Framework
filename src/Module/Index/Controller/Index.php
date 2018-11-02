<?php

namespace Krugozor\Framework\Module\Index\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Framework\Registry;

class Index extends Controller
{
    public function run()
    {
        // Потом удалить. Не ясно откуда берутся эти запросы, но они вредны для SEO.
        if ($this->getRequest()->getGet('page') || $this->getRequest()->getGet('sep')) {
            $this->getResponse()->setHttpStatusCode(301);
            $this->getResponse()->setHeader(Response::HEADER_LOCATION, '/');
            return $this->getResponse();
        }

        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral'
        )->addTitle();

        $this->getView()->regions = $this->getMapper('User/Country')->findCountriesWithRegions();

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->online_users = $this->getMapper('User/User')->findUsersOnline();

        $this->getView()->adverts = $this->getMapper('Advert/Advert')->findLastAdverts(
            Registry::getInstance()->SITE['MAX_ADVERTS_COUNT_ON_INDEX_PAGE'],
            $this->getCurrentUser()
        );

        $this->getView()->vip_adverts = $this->getMapper('Advert/Advert')->findLastVipAdverts(
            Advert::MIN_ADVERTS_WITH_VIP_STATUSES
        );

        $this->getView()->special_adverts = $this->getMapper('Advert/Advert')->findLastSpecialAdverts(
            Advert::MIN_ADVERTS_WITH_SPECIAL_STATUSES
        );

        return $this->getView();
    }
}