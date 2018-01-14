<?php
class Krugozor_Module_Advert_Mapper_Advert extends Krugozor_Mapper_Common
{
    /**
     * Общее условие для выборки объявлений на всех публичных списках.
     *
     * @var string
     */
    const SQL_COMMON_SELECT_LIST_CONDITION = '
            /* объявление не скрыто пользователем */
            AND `advert_active` = 1
            /* пользователь не блокирован */
            AND (`user`.`id` > 0 AND `user`.`user_active` OR `user`.`id` = -1)
            /* объявление оплачено... */
            AND (`advert`.`advert_payment` = 1
            /* ...или не оплачено, но принадлежит текущему пользователю */
               OR
               (
                (`advert`.`advert_id_user` = ?i AND `advert`.`advert_id_user` <> -1)
                OR
                (`advert`.`advert_unique_user_cookie_id` = "?s")
               )
              )';

    /**
     * Общее условие для выборки объявлений на всех публичных местах.
     *
     * @var string
     */
    const SQL_COMMON_SELECT_CONDITION = '
            /* объявление не скрыто пользователем */
            AND `advert_active` = 1
            /* пользователь не блокирован */
            AND (`user`.`id` > 0 AND `user`.`user_active` OR `user`.`id` = -1)
            /* объявление оплачено */
            AND `advert`.`advert_payment` = 1';

    /**
     * @param Krugozor_Module_Advert_Model_Advert $advert
     */
    public function deleteById($advert)
    {
        $advert->deleteThumbnails();
        parent::deleteById($advert);
    }

    /**
     * Очищение vip-дат объявлений с истекшим сроком годности.
     * Не очищает, если кол-во vip-объявлений меньше,
     * чем Krugozor_Module_Advert_Model_Advert::MIN_ADVERTS_WITH_VIP_STATUSES.
     * Метод для cron.
     *
     * @param void
     * @return int количество задействованных рядов
     */
    public function cleanNonActualVipDates()
    {
        $result = $this->getDb()->query(
            'SELECT COUNT(*) FROM ?f WHERE ?f IS NOT NULL',
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('vip_date')
        );
        if ($result->getOne() <= Krugozor_Module_Advert_Model_Advert::MIN_ADVERTS_WITH_VIP_STATUSES) {
            return false;
        }

        $this->getDb()->query('UPDATE ?f SET ?f = NULL WHERE ?f < NOW()',
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('vip_date'),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('vip_date')
        );

        return $this->getDb()->getAffectedRows();
    }

    /**
     * Удаляет неактуальные объявления
     */
    public function deleteNonActualAdverts()
    {
        $sql = '
            select * 
            from advert 
            where 
                advert_create_date < now() - interval 12 month 
                and (
                    advert_edit_date is null 
                    or 
                    advert_edit_date < now() - interval 12 month
                ) 
                and advert_id_user = -1 
                LIMIT 200';
        $list = $this->getMapperManager()->getMapper('Advert/Advert')->findModelListBySql($sql);
        $titles = array();
        foreach ($list as $advert) {
            $titles[] = $advert->getHeader();
            $this->getMapperManager()->getMapper('Advert/Advert')->deleteById($advert);
        }

        return implode(PHP_EOL, $titles);
    }

    /**
     * Очищение special-дат объявлений с истекшим сроком годности.
     * Не очищает, если кол-во vip-объявлений меньше,
     * чем Krugozor_Module_Advert_Model_Advert::MIN_ADVERTS_WITH_SPECIAL_STATUSES.
     * Метод для cron.
     *
     * @param void
     * @return int количество задействованных рядов
     */
    public function cleanNonActualSpecialDates()
    {
        $result = $this->getDb()->query(
            'SELECT COUNT(*) FROM ?f WHERE ?f IS NOT NULL',
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('special_date')
        );
        if ($result->getOne() <= Krugozor_Module_Advert_Model_Advert::MIN_ADVERTS_WITH_SPECIAL_STATUSES) {
            return false;
        }

        $this->getDb()->query('UPDATE ?f SET ?f = NULL WHERE ?f < NOW()',
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('special_date'),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('special_date')
        );

        return $this->getDb()->getAffectedRows();
    }

    /**
     * Если пользователь перед регистрацией подавал на сайт объявления от лица гостя, и хэш-код
     * в поле advert_unique_user_cookie_id совпадает с параметром $user->unique_cookie_id, то
     * назначить всем объявлениям гостя ID данного пользователя.
     *
     * @param Krugozor_Module_User_Model_User $unique_user_cookie_id
     * @return int
     */
    public function updateAdvertsByUniqueUserCookieId(Krugozor_Module_User_Model_User $user)
    {
        $sql = 'UPDATE ?f SET ?f = ?i WHERE ?f = -1 AND ?f = "?s"';

        $this->getDb()->query($sql,
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('id_user'), $user->getId(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('id_user'),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('unique_user_cookie_id'), $user->getUniqueCookieId()
        );

        return $this->getDb()->getAffectedRows();
    }

    /**
     * Обновляет счётчик просмотров объявления на 1.
     *
     * @param Krugozor_Module_Advert_Model_Advert
     * @return int количество задействованных рядов
     */
    public function incrementViewCount(Krugozor_Module_Advert_Model_Advert $advert)
    {
        $sql = 'UPDATE ?f SET ?f = ?f + 1 WHERE `id` = ?i LIMIT 1';

        $this->getDb()->query($sql,
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('view_count'),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('view_count'),
            $advert->getId()
        );

        return $this->getDb()->getAffectedRows();
    }

    /**
     * Возвращает список объектов для вывода в административной части.
     *
     * @param array
     * @return Krugozor_Cover_Array
     */
    public function findListForBackend($params)
    {
        $params = self::makeSqlFromParams($params);

        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    `advert`.`id`,
                    `advert`.`advert_unique_user_cookie_id`,
                    `advert`.`advert_id_user`,
                    `advert`.`advert_active`,
                    `advert`.`advert_vip_date`,
                    `advert`.`advert_special_date`,
                    `advert`.`advert_email`,
                    `advert`.`advert_type`,
                    `advert`.`advert_category`,
                    `advert`.`advert_header`,
                    `advert`.`advert_user_name`,
                    `advert`.`advert_main_user_name`,
                    `advert`.`advert_create_date`,
                    `advert`.`advert_was_moderated`,
                    `advert`.`advert_payment`,
                    `advert`.`advert_thumbnail_count`,
                    `category`.`id`,
                    `category`.`pid`,
                    `category`.`category_indent`,
                    `category`.`category_name`,
                    `category`.`category_url`,
                    `user`.`id`,
                    `user`.`user_first_name`,
                    `user`.`user_last_name`,
                    `user`.`user_login`,
                    `i`.`send_date`
                FROM `advert`
                LEFT JOIN `category` ON `advert`.`advert_category` = `category`.`id`
                LEFT JOIN `user`ON `advert`.`advert_id_user` = `user`.`id`
                LEFT JOIN `user-invite_anonymous_user` i ON i.`unique_cookie_id` = `advert`.`advert_unique_user_cookie_id`
                '.
                $params['where'] .
                $params['order'] .
                $params['limit'];

        array_unshift($params['args'], $sql);

        return parent::result2objects(call_user_func_array(array($this->getDb(), 'query'), $params['args']));
    }

    /**
     * Возвращает список объектов объявлений, "похожих" на объявление
     * $advert пользователя $user.
     *
     * НЕ ИСПОЛЬЗУЕТСЯ!
     *
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @param Krugozor_Module_User_Model_User $user
     * @param int $limit количество возвращаемых записей
     */
    public function finfSimilarAdverts2(Krugozor_Module_Advert_Model_Advert $advert,
                                       Krugozor_Module_User_Model_User $user,
                                       $limit=5,
                                       $start_date_interval=14,
                                       $stop_date_interval=14)
    {
        $params['join'][] = array('LEFT JOIN', 'user', '`user`.`id` = `advert`.`advert_id_user`');
        $params['limit'] = array('start' => 0, 'stop' => $limit);

        $params['where']['`advert_active` = 1 AND `advert_type` = "?s" AND (`user`.`id` > 0 AND `user`.`user_active` OR `user`.`id` = -1)'] = array($advert->getType());
        $params['where']['AND `advert`.`advert_category` = ?i'] = array($advert->getCategory());

        $place = array('city' => $advert->getPlaceCity() ?: $user->getCity(),
                       'region' => $advert->getPlaceRegion() ?: $user->getRegion(),
                       'country' => $advert->getPlaceCountry() ?: $user->getCountry());

        foreach ($place as $key => $value)
        {
            if ($value)
            {
                $params['where']['AND `advert`.`advert_place_'.$key.'` = ?i'] = array($value);
            }
        }

        $params['where']['AND
                              `advert`.`advert_create_date`
                          BETWEEN
                              ("?s" - INTERVAL ?i DAY)
                          AND
                              ("?s" + INTERVAL ?i DAY)'] = array(
        $advert->getCreateDate()->format(Krugozor_Type_Datetime::FORMAT_DATETIME),
        $start_date_interval,
        $advert->getCreateDate()->format(Krugozor_Type_Datetime::FORMAT_DATETIME),
        $stop_date_interval);

        $params['where']['AND `advert`.`id` <> ?i'] = array($advert->getId());

        $params['what'] = '`advert`.`id`,
                           `advert`.`advert_header`,
                           `advert`.`advert_price`,
                           SUBSTRING(`advert`.`advert_text`, 1, 150) AS `advert_text`,
                           `advert`.`advert_create_date`';

        $params['order'] = array('advert.advert_create_date' => 'DESC');

        return parent::findModelListByParams($params);
    }

    /**
     * Возвращает список объектов для вывода в каталоге.
     *
     * @param $params
     * @param bool $use_calc_found_rows использовать ли в запросе параметр SQL_CALC_FOUND_ROWS
     * @return Krugozor_Cover_Array
     */
    public function findListForCatalog($params, Krugozor_Module_User_Model_user $current_user, $use_calc_found_rows=false)
    {
        $params['where'][self::SQL_COMMON_SELECT_LIST_CONDITION] = array($current_user->getId(), $current_user->getUniqueCookieId());

        $params = self::makeSqlFromParams($params);

        if ($params['what'] == ' * ')
        {
            $params['what'] = '';
        }

        $sql = 'SELECT ' . ($use_calc_found_rows ? 'SQL_CALC_FOUND_ROWS' : '') . '
                    IF (`advert`.`advert_vip_date` IS NOT NULL AND `advert`.`advert_vip_date` > NOW(), 1, 0) AS `advert__is_vip`,
                    `advert`.*,
                    `category`.`id`,
                    `category`.`category_name`,
                    `category`.`category_url`,
                    `user-country`.`id`,
                    `user-country`.`country_name_ru`,
                    `user-country`.`country_name_en`,
                    `user-region`.`id`,
                    `user-region`.`id_country`,
                    `user-region`.`region_name_ru`,
                    `user-region`.`region_name_en`,
                    `user-city`.`id`,
                    `user-city`.`id_region`,
                    `user-city`.`id_country`,
                    `user-city`.`city_name_ru`,
                    `user-city`.`city_name_en`
                    ' . $params['what'] . '
                FROM `advert` USE INDEX(adverts_listing)
                INNER JOIN `category` ON `advert`.`advert_category` = `category`.`id`
                LEFT JOIN `user-country` ON `advert`.`advert_place_country` = `user-country`.`id`
                LEFT JOIN `user-region` ON `advert`.`advert_place_region` = `user-region`.`id`
                LEFT JOIN `user-city` ON `advert`.`advert_place_city` = `user-city`.`id`
                LEFT JOIN `user`
                ON `user`.`id` = `advert`.`advert_id_user`
                ' .
                $params['where'] .
                $params['order'] .
                $params['limit'];

        array_unshift($params['args'], $sql);

        return parent::result2objects(call_user_func_array(array($this->getDb(), 'query'), $params['args']));
    }

    /**
     * Возвращает список объектов для вывода в списке объявлений пользователя.
     *
     * @param $params
     * @return Krugozor_Cover_Array
     */
    public function findListForUser($id_user, $start, $stop)
    {
        $sql = 'SELECT SQL_CALC_FOUND_ROWS
                    IF (`advert`.`advert_vip_date` IS NOT NULL AND `advert`.`advert_vip_date` > NOW(), 1, 0)
                        AS `advert__is_vip`,
                    `advert`.*,
                    `category`.`id`,
                    `category`.`category_name`,
                    `category`.`category_url`,
                    `user-country`.`id`,
                    `user-country`.`country_name_ru`,
                    `user-country`.`country_name_en`,
                    `user-region`.`id`,
                    `user-region`.`id_country`,
                    `user-region`.`region_name_ru`,
                    `user-region`.`region_name_en`,
                    `user-city`.`id`,
                    `user-city`.`id_region`,
                    `user-city`.`id_country`,
                    `user-city`.`city_name_ru`,
                    `user-city`.`city_name_en`
                FROM `advert`
                LEFT JOIN `category`
                ON `advert`.`advert_category` = `category`.`id`
                LEFT JOIN `user-country`
                ON `advert`.`advert_place_country` = `user-country`.`id`
                LEFT JOIN `user-region`
                ON `advert`.`advert_place_region` = `user-region`.`id`
                LEFT JOIN `user-city`
                ON `advert`.`advert_place_city` = `user-city`.`id`
                WHERE `advert`.`advert_id_user` = ?i
                ORDER BY `advert__is_vip` DESC, `advert`.`advert_create_date` DESC
                LIMIT ?i, ?i';

        return parent::result2objects(call_user_func_array(array($this->getDb(), 'query'), array($sql, $id_user, $start, $stop)));
    }

    /**
     * Находит объявление по ID + регионы и категорию.
     *
     * @param int ID объявления
     * @return Krugozor_Cover_Array
     */
    public function findByIdForView($id)
    {
        $sql = 'SELECT
                    IF (`advert`.`advert_vip_date` IS NOT NULL AND `advert`.`advert_vip_date` > NOW(), 1, 0)
                        AS advert__is_vip,
                    `advert`.*,
                    `category`.`id`,
                    `category`.`category_name`,
                    `category`.`category_url`,
                    `category`.`category_keywords`,
                    `category`.`category_all_childs`,
                    `user`.id,
                    `user`.`user_group`,
                    `user`.`user_active`,
                    `user`.`user_login`,
                    `user`.`user_password`,
                    `user`.`user_first_name`,
                    `user`.`user_last_name`,
                    `user`.`user_email`,
                    `user`.`user_phone`,
                    `user`.`user_icq`,
                    `user`.`user_url`,
                    `user`.`user_skype`,
                    `user`.`user_city`,
                    `user`.`user_region`,
                    `user`.`user_country`,
                    `user-country`.`id`,
                    `user-country`.`country_name_ru`,
                    `user-country`.`country_name_en`,
                    `user-region`.`id`,
                    `user-region`.`id_country`,
                    `user-region`.`region_name_ru`,
                    `user-region`.`region_name_en`,
                    `user-city`.`id`,
                    `user-city`.`id_region`,
                    `user-city`.`id_country`,
                    `user-city`.`city_name_ru`,
                    `user-city`.`city_name_ru2`,
                    `user-city`.`city_name_en`
                FROM `advert`
                INNER JOIN `category`
                ON `advert`.`advert_category` = `category`.`id`
                LEFT JOIN `user`
                ON `advert`.`advert_id_user` = `user`.`id`
                INNER JOIN `user-country`
                ON `advert`.`advert_place_country` = `user-country`.`id`
                INNER JOIN `user-region`
                ON `advert`.`advert_place_region` = `user-region`.`id`
                INNER JOIN `user-city`
                ON `advert`.`advert_place_city` = `user-city`.`id`
                WHERE `advert`.`id` = ?i
                LIMIT 1';

        $objects = parent::result2objects($this->getDb()->query($sql, $id));

        if (!isset($objects[0]))
        {
            $objects[0] = array();
        }
        else
        {
            // Нужно убить из кэша категорию, что бы функции построения дерева могли взять актуальные
            // данные с загрузкой потомков.
            self::unsetCollectionElement('Category', 'Category', $objects[0]['category']->getId());
        }

        if (!isset($objects[0]['advert']))
        {
            $objects[0]['advert'] = new Krugozor_Module_Advert_Model_Advert();
        }

        if (!isset($objects[0]['user']))
        {
            $objects[0]['user'] = new Krugozor_Module_User_Model_User();
        }

        if (!isset($objects[0]['country']))
        {
            $objects[0]['country'] = new Krugozor_Module_User_Model_Country();
        }

        if (!isset($objects[0]['region']))
        {
            $objects[0]['region'] = new Krugozor_Module_User_Model_Region();
        }

        if (!isset($objects[0]['city']))
        {
            $objects[0]['city'] = new Krugozor_Module_User_Model_City();
        }

        return $objects[0];
    }

    /**
     * @see parent::createModel()
     * @param Krugozor_Module_User_Model_User $user
     */
    public function createModel(Krugozor_Module_User_Model_User $user=null)
    {
        $advert = parent::createModel();

        if ($user) {
            $advert->setIdUser($user->getId());
            $advert->setPlaceCountry($user->getCountry());
            $advert->setPlaceRegion($user->getRegion());
            $advert->setPlaceCity($user->getCity());
        }

        return $advert;
    }

    /**
     * Обновляет дату создания объявления $advert на текущее время -1 секунда.
     * Обновление произойдет только в том случае, если время создания объявления не менее $hour часа назад.
     *
     * @access public
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @param int $hour час времени
     * @param int количество задействованных (обновленных) в запросе строк
     */
    public function updateDateCreate(Krugozor_Module_Advert_Model_Advert $advert, $hour=1)
    {
        $sql = 'UPDATE ?f
                SET ?f = DATE_SUB(NOW(), INTERVAL 1 SECOND), ?f = now()
                WHERE `id` = ?i
                AND NOW() > DATE_ADD(?f, INTERVAL ?i HOUR)';

        $this->getDb()->query($sql,
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('create_date'),
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('edit_date'),
            $advert->id,
            Krugozor_Module_Advert_Model_Advert::getPropertyFieldName('create_date'),
            $hour
        );

        return (int)$this->getDb()->getAffectedRows();
    }

    /**
     * Возвращает предыдущее объявление от текущего.
     *
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function findPrevAdvert(Krugozor_Module_Advert_Model_Advert $advert)
    {
        $params = array(
            'what' => 'advert.*',
            'join' => array(array('inner join', 'user', 'user.id = advert.advert_id_user')),
            'where' => array(
                'advert.id < ?i' => array($advert->getId()),
                'AND' => array(),
                'advert_category = ?i' => array($advert->getCategory()),
                'AND' => array(),
                self::SQL_COMMON_SELECT_CONDITION => array()
            ),
            'order' => array('advert.id' => 'DESC'),
            'limit' => array('start' => 0, 'stop' => 1)
        );

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает следующее объявление от текущего.
     *
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function findNextAdvert(Krugozor_Module_Advert_Model_Advert $advert)
    {
        $params = array(
            'what' => 'advert.*',
            'join' => array(array('inner join', 'user', 'user.id = advert.advert_id_user')),
            'where' => array(
                'advert.id > ?i' => array($advert->getId()),
                'AND' => array(),
                'advert_category = ?i' => array($advert->getCategory()),
                'AND' => array(),
                self::SQL_COMMON_SELECT_CONDITION => array()
            ),
            'order' => array('advert.id' => 'ASC'),
            'limit' => array('start' => 0, 'stop' => 1)
        );

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает объявления для линейки объявлений.
     *
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @param Krugozor_Module_Category_Model_Category $category
     * @return Krugozor_Cover_Array
     */
    public function findLastSimilarAdverts(Krugozor_Module_Advert_Model_Advert $advert, Krugozor_Module_Category_Model_Category $category)
    {
        $data = new Krugozor_Cover_Array();

        $categories = array_merge((array) $category->getId(), $category->getAllChildsAsArray());

        // Сначала получаем последние объявления, спрпава от текущего
        $sql = 'SELECT * FROM `advert`
                INNER JOIN `user` ON `user`.`id` = `advert`.`advert_id_user`
                WHERE `advert`.`advert_category` IN (?ai)
                AND `advert`.`advert_create_date` > "?s"
                AND `advert`.`advert_thumbnail_count` > 0
                ' . self::SQL_COMMON_SELECT_CONDITION . '
                ORDER BY `advert`.`advert_create_date` ASC
                LIMIT 0, 3';

        $result = parent::result2objects(
            call_user_func_array(array($this->getDb(), 'query'), array($sql, $categories, $advert->getCreateDate()->formatAsMysqlDatetime()))
        );

        foreach ($result as $obj) {
            $data->append($obj);
        }

        $right_count = $data->count();

        // Добавляем текущее объявление
        $sql = 'SELECT * FROM `advert` WHERE `advert`.`id` = ?i';

        $result = parent::result2objects(
            call_user_func_array(array($this->getDb(), 'query'), array($sql, $advert->getId()))
        );

        foreach ($result as $obj) {
            $data->prepend($obj);
        }

        // Добавляем предыдущие, объявления слева
        $sql = 'SELECT * FROM `advert`
                JOIN `user` ON `advert`.`advert_id_user` = `user`.`id`
                WHERE `advert`.`advert_category` IN (?ai)
                AND `advert`.`advert_thumbnail_count` > 0
                AND `advert`.`advert_create_date` < "?s"
                ' . self::SQL_COMMON_SELECT_CONDITION . '
                ORDER BY `advert`.`advert_create_date` DESC
                LIMIT 0, ' . (6 - $right_count);

        $result = parent::result2objects(
            call_user_func_array(array($this->getDb(), 'query'), array($sql, $categories, $advert->getCreateDate()->formatAsMysqlDatetime()))
        );

        foreach ($result as $obj) {
            $data->prepend($obj);
        }

        return $data;
    }

    /**
     * Возвращает $limit последних добавленных на сайт объявлений с изображениями.
     *
     * @param int $limit
     * @param Krugozor_Module_User_Model_User $current_user
     * @return Krugozor_Cover_Array
     */
    public function findLastAdverts($limit=15, Krugozor_Module_User_Model_User $current_user)
    {
        $sql = 'SELECT *,
                IF (`advert`.`advert_vip_date` IS NOT NULL AND `advert`.`advert_vip_date` > NOW(), 1, 0) AS `advert__is_vip`,
                `advert`.`id`, `advert`.`advert_vip_date`,
                `advert`.`advert_id_user`,
                `advert`.`advert_unique_user_cookie_id`,
                `advert`.`advert_type`,
                `advert`.`advert_category`,
                `advert`.`advert_header`,
                `advert`.`advert_price`,
                `advert`.`advert_price_type`,
                `advert`.`advert_free`,
                `advert`.`advert_create_date`,
                `advert`.`advert_text`,
                `advert`.`advert_thumbnail_file_name`,
                `advert`.`advert_thumbnail_count`,
                `category`.`id`,
                `category`.`category_name`,
                `category`.`category_url`,
                `user-country`.`id`,
                `user-country`.`country_name_ru`,
                `user-country`.`country_name_en`,
                `user-region`.`id`,
                `user-region`.`id_country`,
                `user-region`.`region_name_ru`,
                `user-region`.`region_name_en`,
                `user-city`.`id`,
                `user-city`.`id_region`,
                `user-city`.`id_country`,
                `user-city`.`city_name_ru`,
                `user-city`.`city_name_en`
            FROM `advert` 
            INNER JOIN `category` ON `advert`.`advert_category` = `category`.`id`
            INNER JOIN `user-country` ON `advert`.`advert_place_country` = `user-country`.`id`
            INNER JOIN `user-region` ON `advert`.`advert_place_region` = `user-region`.`id`
            INNER JOIN `user-city` ON `advert`.`advert_place_city` = `user-city`.`id`
            INNER JOIN `user` ON `user`.`id` = `advert`.`advert_id_user`
            WHERE
                (`advert`.advert_thumbnail_count > 0 OR `advert`.`advert_vip_date` IS NOT NULL) AND
                `category`.category_show_on_index = 1
                ' . self::SQL_COMMON_SELECT_LIST_CONDITION . '
            ORDER BY `advert`.`id` DESC
            LIMIT 0, ?i';

        return parent::result2objects(call_user_func_array(array($this->getDb(), 'query'), array($sql, $current_user->getId(), $current_user->getUniqueCookieId(), $limit)));
    }

    /**
     * Возвращает $limit последних добавленных на сайт объявлений с изображениями и статусом vip.
     *
     * @param int $limit
     * @return Krugozor_Cover_Array
     */
    public function findLastVipAdverts($limit=8)
    {
        $params = array(
            'what' => array('advert.*' => array()),
            'where' => array(
                'advert_vip_date IS NOT NULL
                AND c.category_show_on_index = 1
                AND advert_active = 1
                AND (u.id > 0 AND u.user_active OR u.id = -1)' => array()
            ),
            'join' => array(
                array('inner join', 'category c', 'c.id = advert.advert_category'),
                array('inner join', 'user u', 'u.id = advert.advert_id_user'),
            ),

            'order' => array('advert.advert_vip_date' => 'DESC'),
            'limit' => array('start' => 0, 'stop' => $limit)
        );

        return parent::findModelListByParams($params);
    }

    /**
     * Специальные предложения.
     *
     * @param int $limit
     * @return Krugozor_Cover_Array
     */
	public function findLastSpecialAdverts($limit=10)
    {
        $params = array(
            'what' => array('
                IF (`advert`.`advert_vip_date` IS NOT NULL AND `advert`.`advert_vip_date` > NOW(), 1, 0) AS `advert__is_vip`,
                advert.*, 
                c.*, user.*, `user-country`.*, `user-region`.*, `user-city`.*' => array()),
            'where' => array(
                '`advert_special_date` IS NOT NULL' . self::SQL_COMMON_SELECT_CONDITION => array()
            ),
            'join' => array(
                array('inner join', 'category c', 'c.id = advert.advert_category'),
                array('inner join', 'user', 'user.id = advert.advert_id_user'),
                array('inner join', '`user-country`', '`advert`.`advert_place_country` = `user-country`.`id`'),
                array('inner join', '`user-region`',  '`advert`.`advert_place_region` = `user-region`.`id`'),
                array('inner join', '`user-city`',    '`advert`.`advert_place_city` = `user-city`.`id`'),
            ),
            'order' => array('advert.advert_special_date' => 'DESC'),
            'limit' => array('start' => 0, 'stop' => $limit)
        );

        return parent::result2objects(parent::createQuerySelect($params));
    }

    /**
     * Установка статуса "оплачено" для тех объявлений, которе не оплатили своё объявление в последние
     * Krugozor_Module_Advert_Model_Advert::PAID_TOLERANCE_DAYS дней.
     * Метод для cron.
     *
     * @param void
     * @return int
     */
    public function setPaidTolerance()
    {
        $sql = 'UPDATE ?f
                SET `advert_payment` = 1
                WHERE `advert_payment` = 0
                AND `advert_category` IN (SELECT `id` FROM `category` WHERE `category_paid` = 1)
                AND `advert_create_date` < NOW() - INTERVAL ?i DAY';

        $this->getDb()->query($sql, $this->getTableName(), Krugozor_Module_Advert_Model_Advert::PAID_TOLERANCE_DAYS);

        return $this->getDb()->getAffectedRows();
    }
}