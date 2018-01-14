<?php

class Krugozor_Module_User_Model_InviteAnonymousUser extends Krugozor_Model
{
    protected static $model_attributes = [
        'id' => [
            'db_element' => false,
            'default_value' => 0,
            'validators' => ['Decimal' => ['signed' => true]],
        ],

        'unique_cookie_id' => [
            'db_element' => true,
            'db_field_name' => 'unique_cookie_id',
            'record_once' => true,
            'validators' => [
                'StringLength' => [
                    'start' => Krugozor_Validator_StringLength::MD5_MAX_LENGTH,
                    'stop' => Krugozor_Validator_StringLength::MD5_MAX_LENGTH
                ],
                'CharPassword' => [],
            ]
        ],

        'send_date' => [
            'db_element' => true,
            'db_field_name' => 'send_date',
            'type' => 'Krugozor_Type_Datetime',
            'validators' => ['DateCorrect' => ['format' => Krugozor_Type_Datetime::FORMAT_DATETIME]],
        ],

    ];
}