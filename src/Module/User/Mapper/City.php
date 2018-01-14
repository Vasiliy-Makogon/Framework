<?php

class Krugozor_Module_User_Mapper_City extends Krugozor_Mapper_Common
{
    /**
     * Метод для получения списка городов для Ajax-ответа.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getListForSelectOptions($id_region = 1)
    {
        if (!Krugozor_Static_Numeric::is_decimal($id_region)) {
            $id_region = 1;
        }

        $field_city_name = 'city_name_' . Krugozor_Registry::getInstance()->LOCALIZATION['LANG'];

        $sql = 'SELECT `id`, ?f FROM ?f WHERE `id_region` = ?i ORDER BY `weight` DESC';

        $result = parent::findModelListBySql($sql, $field_city_name, $this->getTableName(), $id_region);

        $data = new Krugozor_Cover_Array();

        if ($result->count()) {
            foreach ($result as $element) {
                $data->append(array($element->getId(), $element->getNameRu()));
            }
        }

        return $data;
    }

    /**
     * Находит город по имени в транслите и объекту региона.
     * Второй параметр необходим для того, что бы исключить нахождение городов с аналогичными названиями.
     *
     * @param string $name_en
     * @param Krugozor_Module_User_Model_Region $region
     * @return Krugozor_Model
     */
    public function findByNameEnAndRegion($name_en, Krugozor_Module_User_Model_Region $region)
    {
        $params = array
        (
            'where' => array('?f = "?s" AND ?f = ?i' => array(
                Krugozor_Module_User_Model_City::getPropertyFieldName('name_en'), $name_en,
                Krugozor_Module_User_Model_City::getPropertyFieldName('id_region'), $region->getId(),
            ))
        );

        return parent::findModelByParams($params);
    }

    /**
     * Получение списка городов региона в которыъ есть объявления.
     *
     * @param Krugozor_Module_User_Model_Region $region
     * @return Krugozor_Cover_Array
     */
    public function getListByRegion(Krugozor_Module_User_Model_Region $region)
    {
        $sql = 'SELECT *, SUM(`count`) as `advert-city_count__join_count`
                FROM  `user-city` c
                JOIN  `advert-city_count` ac ON c.id = ac.id_city
                WHERE c.id_region = ?i
                GROUP BY id_city
                ORDER BY c.weight DESC, c.city_name_ru ASC';

        return parent::join($sql, $region->getId());
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
}