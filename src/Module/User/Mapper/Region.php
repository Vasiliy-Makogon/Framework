<?php

namespace Krugozor\Framework\Module\User\Mapper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Statical\Numeric;
use Krugozor\Framework\Module\User\Model\Country as CountryModel;
use Krugozor\Framework\Module\User\Model\Region as RegionModel;

class Region extends CommonMapper
{
    /**
     * Активные регионы.
     * @var string
     */
    CONST SQL_FIND_LIST_ACTIVE_REGION = '
            SELECT r.`id`, r.`region_name_ru`
            FROM ?f r
            JOIN `user-country` c ON c.id = r.id_country 
            ORDER BY c.`weight` DESC, r.`weight` DESC';

    /**
     * Метод для получения списка регионов для Ajax-ответа.
     *
     * @return CoverArray
     */
    public function getListForSelectOptions($id_country = 1)
    {
        if (!Numeric::isDecimal($id_country)) {
            $id_country = 1;
        }

        $field_region_name = 'name_' . Registry::getInstance()->LOCALIZATION['LANG'];
        $sql = 'SELECT `id`, ?f FROM ?f WHERE `id_country` = ?i ORDER BY `weight` DESC';
        $result = parent::findModelListBySql(
            $sql,
            RegionModel::getPropertyFieldName($field_region_name),
            $this->getTableName(),
            $id_country
        );

        $data = new CoverArray();

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
     * @return CoverArray
     */
    public function findListForBackend(array $params = array())
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }

    /**
     * Находит регион по имени в транслите.
     *
     * @param $name_en
     * @return RegionModel
     */
    public function findByNameEn($name_en)
    {
        $params = [
            'where' => [
                '?f = "?s"' => [RegionModel::getPropertyFieldName('name_en'), $name_en]
            ]
        ];

        return parent::findModelByParams($params);
    }

    /**
     * Возвращает список активных регионов.
     *
     * @return CoverArray
     */
    public function getListActiveRegion()
    {
        return parent::findModelListBySql(self::SQL_FIND_LIST_ACTIVE_REGION, $this->getTableName());
    }

    /**
     * Получение списка регионов страны в которых есть объявления.
     *
     * @param CountryModel $country
     * @return CoverArray
     */
    public function getListByCountry(CountryModel $country)
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