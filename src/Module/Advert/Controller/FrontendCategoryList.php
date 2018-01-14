<?php
class Krugozor_Module_Advert_Controller_FrontendCategoryList extends Krugozor_Controller
{
    public function run()
    {
        $this->getView()->getLang()->loadI18n(
            'Common/FrontendGeneral',
            'Advert/FrontendCommon',
            $this->getRequest()->getVirtualControllerPath()
        )->addTitle();

        $category = $this->getMapper('Category/Category')->findByUrl(
            $this->getRequest()->getRequest('category_url')
        );

        if (!$category->getId()) {
            return $this->createNotification()
                        ->setType(Krugozor_Notification::TYPE_ALERT)
                        ->setMessage($this->getView()->getLang()['notification']['message']['element_does_not_exist'])
                        ->setNotificationUrl('/categories/')
                        ->run();
        }

        $pagination = Krugozor_Pagination_Adapter::getManager($this->getRequest(), 50, 10);

        // Получаем дерево объектов от корневой категории до категории $category->getId()
        $path_to_category = $this->getMapper('Category/Category')->loadPath($category->getId());
        $this->getView()->getHelper('Krugozor_Html_Title')->add($category->getName());

        // ID категорий, по которым будет производиться поиск объявлений.
        $ids = $category->getAllChildsAsArray();
        $ids[] = $category->getId();

        $params = array(
            'where' => count($ids) > 1
            ? array('?f IN (?ai)' => array(Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('category'), $ids))
            : array('?f = ?i' => array(Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('category'), $ids[0])),
            'limit' => array('start' => $pagination->getStartLimit(), 'stop' => $pagination->getStopLimit())
        );

        $this->getView()->territories = new Krugozor_Module_User_Cover_TerritoryList();

        // Получаем регионы из URL
        if ($country_name = $this->getRequest()->getRequest('country_name_en')) {
            $country = $this->getMapper('User/Country')->findByNameEn($country_name);

            if (!$country->getId()) {
                $this->getView()->getLang()->loadI18n('404/404')->addTitle();
                $this->getResponse()->setHttpStatusCode(404);

                $this->getView()->current_user = $this->getCurrentUser();
                $this->getView()->setTemplateFile(DOCUMENTROOT_PATH .  '/Krugozor/Module/404/Template/404.phtml');
                return $this->getView();
            }

            $params['where']['AND `' . Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('place_country') . '` = ?i'] = array($country->getId());
            $this->getView()->territories->country = $country;
        }

        if ($this->getView()->territories->country && $region_name = $this->getRequest()->getRequest('region_name_en')) {
            $region = $this->getMapper('User/Region')->findByNameEn($region_name);

            if (!$region->getId()) {
                $this->getView()->getLang()->loadI18n('404/404')->addTitle();
                $this->getResponse()->setHttpStatusCode(404);

                $this->getView()->current_user = $this->getCurrentUser();
                $this->getView()->setTemplateFile(DOCUMENTROOT_PATH .  '/Krugozor/Module/404/Template/404.phtml');
                return $this->getView();
            }

            $params['where']['AND `' . Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('place_region') . '` = ?i'] = array($region->getId());
            $this->getView()->territories->region = $region;
        }

        if ($this->getView()->territories->country && $this->getView()->territories->region && $city_name = $this->getRequest()->getRequest('city_name_en')) {
            $city = $this->getMapper('User/City')->findByNameEnAndRegion($city_name, $this->getView()->territories->region);

            if (!$city->getId()) {
                $this->getView()->getLang()->loadI18n('404/404')->addTitle();
                $this->getResponse()->setHttpStatusCode(404);

                $this->getView()->current_user = $this->getCurrentUser();
                $this->getView()->setTemplateFile(DOCUMENTROOT_PATH .  '/Krugozor/Module/404/Template/404.phtml');
                return $this->getView();
            }

            $params['where']['AND `' . Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('place_city') . '` = ?i'] = array($city->getId());
            $this->getView()->territories->city = $city;
        }

        $use_calc_found_rows = false;

        if ($this->getRequest()->getGet('type', 'string')) {
            $use_calc_found_rows = true;
            $params['where']['AND `' . Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('type') . '` = "?s"'] = array($this->getRequest()->getGet('type', 'string'));
        }

        if ($this->getRequest()->getRequest('search', 'string')) {
            $use_calc_found_rows = true;
            $params['what'][', ROUND(MATCH (`advert_header`, `advert_text`) AGAINST ("?s"), 2) as `advert__score`'] = array(
                $this->getRequest()->getRequest('search', 'string')
            );
            $params['where']['AND MATCH (`advert_header`, `advert_text`) AGAINST ("?s")'] = array(
                $this->getRequest()->getRequest('search', 'string')
            );
            $params['order']['advert__score'] = 'DESC';
        }

        $this->getView()->region_paths = new Krugozor_Cover_array();

        if ($this->getView()->territories->country) {
            $this->getView()->bread_crumbs_postfix_text = $this->getView()->territories->country->getIsDefaultCountry()
                                                          ? null
                                                          : $this->getView()->getLang()['content']['in'] .
                                                            $this->getView()->territories->country->getNameRu2();
        }

        if ($this->getView()->territories->country && $this->getView()->territories->region) {
            $this->getView()->bread_crumbs_postfix_text = $this->getView()->getLang()['content']['in'] .
                                                          $this->getView()->territories->region->getNameRu2();
        }

        if ($this->getView()->territories->country && $this->getView()->territories->region && $this->getView()->territories->city) {
            $this->getView()->bread_crumbs_postfix_text = $this->getView()->getLang()['content']['in'] .
                                                          $this->getView()->territories->city->getNameRu2();
        }

        $this->getView()->getHelper('Krugozor_Html_Title')->addPostfixInLastElement($this->getView()->bread_crumbs_postfix_text);

        $params['order']['advert__is_vip'] = 'DESC';
        $params['order']['advert.advert_create_date'] = 'DESC';

        $this->getView()->adverts = $this->getMapper('Advert/Advert')->findListForCatalog($params, $this->getCurrentUser(), $use_calc_found_rows);

        $count = $use_calc_found_rows
                 ? $this->getMapper('Advert/Advert')->getFoundRows()
                 : $this->getMapper('Category/Regions')->findAdvertCountInRegionCategory(
                       $this->getView()->territories->getLast(), $category
                   );
        $pagination->setCount($count);

        // Субкатегории данного раздела.
        $this->getView()->subcategories = $this->getMapper('Category/Regions')->findCategoriesByIdsWithCountElements(
            $category->getChildsAsArray(), $this->getView()->territories->getLast()
        );

        $this->getView()->pagination = $pagination;
        $this->getView()->current_user = $this->getCurrentUser();
        $this->getView()->category = $category;
        $this->getView()->path_to_category = $path_to_category;

        $this->getView()->online_users = $this->getMapper('User/User')->findUsersOnline();

        // Основные категории для подвала.
        if ($this->getView()->category->isTopCategory()) {
            $this->getView()->categories = $this->getMapper('Category/Regions')->findCategoriesFirstLevelWithCountElements(
                $this->getView()->territories->getLast()
            );
        }

        return $this->getView();
    }
}