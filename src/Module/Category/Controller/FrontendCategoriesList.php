<?php

namespace Krugozor\Framework\Module\Category\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Framework\Module\User\Cover\TerritoryList;
use Krugozor\Framework\Pagination\Adapter;

class FrontendCategoriesList extends Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral', 'Advert/FrontendCategoryList', $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $pagination = Adapter::getManager($this->getRequest(), 50, 10);
        $params = array(
            'where' => array('1=1' => array()),
            'limit' => array('start' => $pagination->getStartLimit(), 'stop' => $pagination->getStopLimit())
        );

        $this->getView()->territories = new TerritoryList();

        if ($country_name = $this->getRequest()->getRequest('country_name_en')) {
            $country = $this->getMapper('User/Country')->findByNameEn($country_name);

            if (!$country->getId()) {
                $this->getView()->getLang()->loadI18n('NotFound/NotFound')->addTitle();
                $this->getResponse()->setHttpStatusCode(404);

                $this->getView()->current_user = $this->getCurrentUser();
                $this->getView()->setTemplateFile(DOCUMENTROOT_PATH . '/Krugozor/Framework/Module/NotFound/Template/NotFound.phtml');
                return $this->getView();
            }

            $params['where']['AND `' . Advert::getPropertyFieldName('place_country') . '` = ?i'] = array($country->getId());
            $this->getView()->territories->country = $country;
        }

        if ($region_name = $this->getRequest()->getRequest('region_name_en')) {
            $region = $this->getMapper('User/Region')->findByNameEn($region_name);

            if (!$region->getId()) {
                $this->getView()->getLang()->loadI18n('NotFound/NotFound')->addTitle();
                $this->getResponse()->setHttpStatusCode(404);

                $this->getView()->current_user = $this->getCurrentUser();
                $this->getView()->setTemplateFile(DOCUMENTROOT_PATH . '/Krugozor/Framework/Module/NotFound/Template/NotFound.phtml');
                return $this->getView();
            }

            $params['where']['AND `' . Advert::getPropertyFieldName('place_region') . '` = ?i'] = array($region->getId());
            $this->getView()->territories->region = $region;
        }

        if ($this->getView()->territories->region && $city_name = $this->getRequest()->getRequest('city_name_en')) {
            $city = $this->getMapper('User/City')->findByNameEnAndRegion($city_name, $this->getView()->territories->region);

            if (!$city->getId()) {
                $this->getView()->getLang()->loadI18n('NotFound/NotFound')->addTitle();
                $this->getResponse()->setHttpStatusCode(404);

                $this->getView()->current_user = $this->getCurrentUser();
                $this->getView()->setTemplateFile(DOCUMENTROOT_PATH . '/Krugozor/Framework/Module/NotFound/Template/NotFound.phtml');
                return $this->getView();
            }

            $params['where']['AND `' . Advert::getPropertyFieldName('place_city') . '` = ?i'] = array($city->getId());
            $this->getView()->territories->city = $city;
        }

        $params['order']['advert__is_vip'] = 'DESC';
        $params['order']['advert.advert_create_date'] = 'DESC';

        $this->getView()->adverts = $this->getMapper('Advert/Advert')->findListForCatalog($params, $this->getCurrentUser(), false);

        $count = $this->getMapper('Category/Regions')->findAdvertCountInRegion(
            $this->getView()->territories->getLast()
        );
        $this->getView()->pagination = $pagination->setCount($count);

        if ($this->getView()->territories->country) {
            $this->getView()->bread_crumbs_postfix_text2 = $this->getView()->territories->country->getIsDefaultCountry()
                ? null
                : $this->getView()->getLang()['content']['in'] .
                $this->getView()->territories->country->getNameRu2();

            $this->getView()->bread_crumbs_postfix_text = $this->getView()->territories->country->getIsDefaultCountry()
                ? null
                : $this->getView()->territories->country->getNameRu();
        }

        if ($this->getView()->territories->country && $this->getView()->territories->region) {
            $this->getView()->bread_crumbs_postfix_text2 = $this->getView()->getLang()['content']['in'] .
                $this->getView()->territories->region->getNameRu2();

            $this->getView()->bread_crumbs_postfix_text = $this->getView()->territories->region->getNameRu();
        }

        if ($this->getView()->territories->country && $this->getView()->territories->region && $this->getView()->territories->city) {
            $this->getView()->bread_crumbs_postfix_text2 = $this->getView()->getLang()['content']['in'] .
                $this->getView()->territories->city->getNameRu2();

            $this->getView()->bread_crumbs_postfix_text = $this->getView()->territories->city->getNameRu();
        }

        $this->getView()->categories = $this->getMapper('Category/Regions')->findCategoriesFirstLevelWithCountElements(
            $this->getView()->territories->getLast()
        );

        $this->getView()->getHelper('\Krugozor\Framework\Html\Title')->addPostfixInLastElement(
            $this->getView()->bread_crumbs_postfix_text2
        );

        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->online_users = $this->getMapper('User/User')->findUsersOnline();

        if (!empty($country) && empty($region)) {
            $this->getView()->regions = $this->getMapper('User/Region')->getListByCountry($country);
        } else if (!empty($region) && empty($city)) {
            $this->getView()->cities = $this->getMapper('User/City')->getListByRegion($region);
        }

        return $this->getView();
    }
}