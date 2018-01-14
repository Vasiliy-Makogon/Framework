<?php
class Krugozor_Module_Test_Model_Test extends Krugozor_Model
{
    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0,
            'validators' => array(
                'Common/Decimal' => array('signed' => false),
            )
        ),

        'value' => array(
            'db_element' => true,
            'db_field_name' => 'test_value',
            'default_value'=> null,
            'validators' => array(
                'Common/IsNotEmpty' => array(),
                'Common/HasBadUrl' => array('break' => false),
                'Common/HasBadEmail' => array('break' => false),
                'Common/StringLength' => array('start'=> 0, 'stop' => 12),
            )
        ),
    );
}