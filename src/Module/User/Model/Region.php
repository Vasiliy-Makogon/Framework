<?php

namespace Krugozor\Framework\Module\User\Model;

use Krugozor\Framework\Statical\Translit;

class Region extends Territory
{
    protected static $db_field_prefix = 'region';

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

        'is_important_region' => array(
            'db_element' => true,
            'db_field_name' => 'is_important_region',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
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

        // Имя региона в именительном падеже.
        'name_ru' => array(
            'db_element' => true,
            'db_field_name' => 'region_name_ru',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),

        'name_ru2' => array(
            'db_element' => true,
            'db_field_name' => 'region_name_ru2',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),

        'name_ru3' => array(
            'db_element' => true,
            'db_field_name' => 'region_name_ru3',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),

        'name_en' => array(
            'db_element' => true,
            'db_field_name' => 'region_name_en',
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => 50),
            ),
        ),
    );

    /**
     * Устанавливает имя региона на латинице, транслитилируя значение.
     * explicit-метод.
     *
     * @param string $name_ru имя региона
     * @return string имя региона в транслите
     */
    protected function _setNameEn($name_ru)
    {
        return Translit::UrlTranslit($name_ru);
    }
}