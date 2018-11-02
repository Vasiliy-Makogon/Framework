<?php

namespace Krugozor\Framework\Module\User\Model;

use Krugozor\Framework\Statical\Translit;

class City extends Territory
{
    protected static $db_field_prefix = 'city';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0
        ),

        'weight' => array(
            'db_element' => true,
            'default_value' => 0,
            'db_field_name' => 'weight',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'has_metro' => array(
            'db_element' => true,
            'db_field_name' => 'has_metro',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'id_region' => array(
            'db_element' => true,
            'default_value' => 0,
            'db_field_name' => 'id_region',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'id_country' => array(
            'db_element' => true,
            'default_value' => 0,
            'db_field_name' => 'id_country',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'name_ru' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_ru',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),

        'name_ru2' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_ru2',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),

        'name_ru3' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_ru3',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),

        'name_en' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_en'
        ),
    );

    /**
     * Устанавливает имя города на латинице, транслитилируя значение.
     * explicit-метод.
     *
     * @param string $name_ru имя города
     * @return string имя города в транслите
     */
    protected function _setNameEn($name_ru)
    {
        return Translit::UrlTranslit($name_ru);    }
}