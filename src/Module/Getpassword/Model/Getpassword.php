<?php

namespace Krugozor\Framework\Module\Getpassword\Model;

use Krugozor\Framework\Model;
use Krugozor\Framework\Type\Datetime;
use Krugozor\Framework\Validator\StringLength;

class Getpassword extends Model
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
            'record_once' => true,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'hash' => array(
            'db_element' => true,
            'record_once' => true,
            'validators' => array(
                'StringLength' => array(
                    'start' => StringLength::MD5_MAX_LENGTH,
                    'stop' => StringLength::MD5_MAX_LENGTH
                ),
                'CharPassword' => array(),
            )
        ),

        'date' => array(
            'type' => 'Krugozor\Framework\Type\Datetime',
            'db_element' => true,
            'default_value' => 'now',
            'validators' => array(
                'DateCorrect' => array('format' => Datetime::FORMAT_DATETIME),
            )
        ),
    );
}