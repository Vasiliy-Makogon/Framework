<?php

namespace Krugozor\Framework\Module\User\Mapper;

use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Registry;
use Krugozor\Framework\Statical\Numeric;
use Krugozor\Framework\Module\User\Model\City as CityModel;
use Krugozor\Framework\Module\User\Model\Region as RegionModel;
use Krugozor\Cover\CoverArray;

class City extends CommonMapper
{
    /**
     * Метод для получения списка городов для Ajax-ответа.
     *
     * @return CoverArray
     */
    public function getListForSelectOptions($id_region = 1)
    {
        if (!Numeric::isDecimal($id_region)) {
            $id_region = 1;
        }

        $field_city_name = 'name_' . Registry::getInstance()->LOCALIZATION['LANG'];
        $sql = 'SELECT `id`, ?f FROM ?f WHERE `id_region` = ?i ORDER BY `weight` DESC';
        $result = parent::findModelListBySql(
            $sql,
            CityModel::getPropertyFieldName($field_city_name),
            $this->getTableName(),
            $id_region
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
     * Находит город по имени в транслите и объекту региона.
     * Второй параметр необходим для того, что бы исключить нахождение городов с аналогичными названиями.
     *
     * @param $name_en
     * @param RegionModel $region
     * @return CityModel
     */
    public function findByNameEnAndRegion($name_en, RegionModel $region)
    {
        $params = [
            'where' => [
                '?f = "?s" AND ?f = ?i' => [
                    CityModel::getPropertyFieldName('name_en'), $name_en,
                    CityModel::getPropertyFieldName('id_region'), $region->getId(),
                ]
            ]
        ];

        return parent::findModelByParams($params);
    }

    /**
     * Получение списка городов региона в которыъ есть объявления.
     *
     * @param RegionModel $region
     * @return bool|false|CoverArray
     */
    public function getListByRegion(RegionModel $region)
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
     * @return CoverArray
     */
    public function findListForBackend(array $params = array())
    {
        $params['what'] = 'SQL_CALC_FOUND_ROWS *';

        return parent::findModelListByParams($params);
    }
}