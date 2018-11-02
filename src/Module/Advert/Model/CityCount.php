<?php

namespace Krugozor\Framework\Module\Advert\Model;

use Krugozor\Framework\Model;

class CityCount extends Model
{
    protected static $model_attributes = array
    (
        'id_city' => array(
            'db_element' => true,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'id_category' => array(
            'db_element' => true,
            'validators' => array(
                'IsNotEmpty' => array(),
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