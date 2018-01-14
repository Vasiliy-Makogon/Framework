<?php

class Krugozor_Module_Group_Model_Access extends Krugozor_Model
{
    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'id_group' => array(
            'db_element' => true,
            'db_field_name' => 'id_group',
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => true),
            )
        ),

        'id_controller' => array(
            'db_element' => true,
            'db_field_name' => 'id_controller',
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => true),
            )
        ),

        'access' => array(
            'db_element' => true,
            'db_field_name' => 'access',
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => true),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),
    );
}