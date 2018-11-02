<?php

namespace Krugozor\Framework\Module\User\Model;

use Krugozor\Framework\Model;
use Krugozor\Framework\Type\Datetime;
use Krugozor\Framework\Validator\StringLength;

class InviteAnonymousUser extends Model
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
                    'start' => StringLength::MD5_MAX_LENGTH,
                    'stop' => StringLength::MD5_MAX_LENGTH
                ],
                'CharPassword' => [],
            ]
        ],

        'send_date' => [
            'db_element' => true,
            'db_field_name' => 'send_date',
            'type' => 'Krugozor\Framework\Type\Datetime',
            'validators' => ['DateCorrect' => ['format' => Datetime::FORMAT_DATETIME]],
        ],
    ];
}