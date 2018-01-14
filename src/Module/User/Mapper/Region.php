<?php

class Krugozor_Module_User_Mapper_Region extends Krugozor_Mapper_Common
{
    CONST SQL_FIND_LIST_ACTIVE_REGION = '
            SELECT r.`id`, r.`region_name_ru`
            FROM ?f r
            JOIN `user-country` c ON c.id = r.id_country 
            ORDER BY c.`weight` DESC, r.`weight` DESC';

    /**
     * Метод для получения списка регионов для Ajax-ответа.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getListForSelectOptions($id_country = 1)
    {
        if (!Krugozor_Static_Numeric::is_decimal($id_country)) {
            $id_country = 1;
        }

        $field_region_name = 'region_name_' . Krugozor_Registry::getInstance()->LOCALIZATION['LANG'];

        $sql = 'SELECT `id`, ?f FROM ?f WHERE `id_country` = ?i ORDER BY `weight` DESC';

        $result = parent::findModelListBySql($sql, $field_region_name, $this->getTableName(), $id_country, $field_region_name);

        $data = new Krugozor_Cover_Array();

        if ($result->count()) {
            foreach ($result as $element) {
                $data->append(array($element->getId(), $element->getNameRu()));
            }
        }

        return $data;
    }


    /**
     * Возвращает список записей для админитративной части.
     *
     * @param array $params
     * @return Krugozor_Cover_Array
     */
    public function findListForBackend(array $params = array())
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }

    /**
     * Находит регион по имени в транслите.
     *
     * @param string $name_en
     * @return Krugozor_Model
     */
    public function findByNameEn($name_en)
    {
        $params = array
        (
            'where' => array('?f = "?s"' => array(Krugozor_Module_User_Model_Region::getPropertyFieldName('name_en'), $name_en))
        );

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает список активных регионов.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getListActiveRegion()
    {
        return parent::findModelListBySql(self::SQL_FIND_LIST_ACTIVE_REGION, $this->getTableName());
    }

    /**
     * Получение списка регионов страны в которых есть объявления.
     *
     * @param Krugozor_Module_User_Model_Country $country
     * @return Krugozor_Cover_Array
     */
    public function getListByCountry(Krugozor_Module_User_Model_Country $country)
    {
        $sql = 'SELECT *, SUM(`count`) as `advert-region_count__join_count`
                FROM  `user-region` r
                JOIN  `advert-region_count` ar ON r.id = ar.id_region
                WHERE r.id_country = ?i
                GROUP BY id_region
                ORDER BY r.weight DESC, r.region_name_ru ASC';

        return parent::join($sql, $country->getId());
    }
}