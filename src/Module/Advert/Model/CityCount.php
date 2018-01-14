<?php
class Krugozor_Module_Advert_Model_CityCount extends Krugozor_Model
{
    protected static $model_attributes = array
    (
        'id_city' => array(
            'db_element' => true,
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'id_category' => array(
            'db_element' => true,
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'count' => array(
            'db_element' => true,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'join_count' => array(
            'db_element' => false,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),
    );
}