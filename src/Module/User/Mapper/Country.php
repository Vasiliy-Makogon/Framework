<?php

namespace Krugozor\Framework\Module\User\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Module\User\Model\Country as CountryModel;

class Country extends CommonMapper
{
    /**
     * Активные страны.
     * @var string
     */
    CONST SQL_FIND_LIST_ACTIVE_COUNTRY = '
            SELECT `id`, `country_name_ru`
            FROM ?f
            WHERE `country_active` = 1
            ORDER BY `weight` DESC';

    /**
     * Метод для получения списка активных стран для Ajax-ответа.
     *
     * @return CoverArray
     */
    public function getListForSelectOptions()
    {
        $result = parent::findModelListBySql(self::SQL_FIND_LIST_ACTIVE_COUNTRY, $this->getTableName());

        $data = new CoverArray();

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
     * @return CoverArray
     */
    public function findCountriesWithRegions()
    {
        $sql = '
            SELECT c.*, r.* 
            FROM ?f as c, ?f as r 
            WHERE c.weight >= 250 
            AND c.id = r.id_country 
            ORDER BY c.`weight` DESC, r.`weight` DESC';

        $result = $this->join(
            $sql,
            $this->getTableName(),
            $this->getMapperManager()->getMapper('User/Region')->getTablename()
        );

        $data = new CoverArray();

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
     * @return CoverArray
     */
    public function getListActiveCountry()
    {
        return parent::findModelListBySql(self::SQL_FIND_LIST_ACTIVE_COUNTRY, $this->getTableName());
    }

    /**
     * Находит страну по имени в транслите.
     *
     * @param string $name_en
     * @return CountryModel
     */
    public function findByNameEn($name_en)
    {
        $params = [
            'where' => [
                '?f = "?s"' => [CountryModel::getPropertyFieldName('name_en'), $name_en]
            ]
        ];

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает список записей для админитративной части.
     *
     * @param array $params
     * @return CoverArray
     */
    public function findListForBackend(array $params = array())
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }
}