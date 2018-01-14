<?php

class Krugozor_Module_User_Model_City extends Krugozor_Module_User_Model_Territory
{
    protected static $db_field_prefix = 'city';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0
        ),

        // Вес для сортировки
        'weight' => array(
            'db_element' => true,
            'default_value' => 0,
            'db_field_name' => 'weight',
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'has_metro' => array(
            'db_element' => true,
            'db_field_name' => 'has_metro',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'id_region' => array(
            'db_element' => true,
            'default_value' => 0,
            'db_field_name' => 'id_region'
        ),

        'id_country' => array(
            'db_element' => true,
            'default_value' => 0,
            'db_field_name' => 'id_country'
        ),

        'name_ru' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_ru'
        ),

        'name_ru2' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_ru2'
        ),

        'name_ru3' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_ru3'
        ),

        'name_en' => array(
            'db_element' => true,
            'db_field_name' => 'city_name_en'
        ),
    );


}