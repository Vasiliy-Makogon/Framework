<?php

class Krugozor_Module_User_Mapper_Country extends Krugozor_Mapper_Common
{
    CONST SQL_FIND_LIST_ACTIVE_COUNTRY = '
            SELECT `id`, `country_name_ru`
            FROM ?f
            WHERE `country_active` = 1
            ORDER BY `weight` DESC';

    /**
     * Метод для получения списка активных стран для Ajax-ответа.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getListForSelectOptions()
    {
        $result = parent::findModelListBySql(self::SQL_FIND_LIST_ACTIVE_COUNTRY, $this->getTableName());

        $data = new Krugozor_Cover_Array();

        if ($result->count()) {
            foreach ($result as $element) {
                $data->append(array($element->getId(), $element->getNameRu()));
            }
        }

        return $data;
    }

    /**
     * Возвращает страны с регионами.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function findCountriesWithRegions()
    {
        $sql = 'SELECT c.*, r.* FROM ?f as c, ?f as r WHERE c.weight >= 250 and c.id = r.id_country ORDER BY c.`weight` DESC, r.`weight` DESC';

        $result = $this->join($sql, $this->getTableName(), 'user-region');

        $data = new Krugozor_Cover_Array();

        foreach ($result as $row) {
            if (!isset($data[$row['country']->getId()])) {
                $data[$row['country']->getId()]['country'] = $row['country'];
            }

            $data[$row['country']->getId()]['region'][] = $row['region'];
        }

        return $data;
    }

    /**
     * Возвращает список активных стран.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getListActiveCountry()
    {
        return parent::findModelListBySql(self::SQL_FIND_LIST_ACTIVE_COUNTRY, $this->getTableName());
    }

    /**
     * Находит страну по имени в транслите.
     *
     * @param string $name_en
     * @return Krugozor_Model
     */
    public function findByNameEn($name_en)
    {
        $params = array
        (
            'where' => array('?f = "?s"' => array(Krugozor_Module_User_Model_Country::getPropertyFieldName('name_en'), $name_en))
        );

        return parent::findModelByParams($params);
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