<?php
class Krugozor_Module_Getpassword_Model_Getpassword extends Krugozor_Model
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

        'user_id' => array(
            'db_element' => true,
            'db_field_name' => 'user_id',
            'record_once' => true,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'hash' => array(
            'db_element' => true,
            'db_field_name' => 'hash',
            'record_once' => true,
            'validators' => array(
                'StringLength' => array(
                    'start' => Krugozor_Validator_StringLength::MD5_MAX_LENGTH,
                    'stop' => Krugozor_Validator_StringLength::MD5_MAX_LENGTH
                ),
                'CharPassword' => array(),
            )
        ),
    );
}