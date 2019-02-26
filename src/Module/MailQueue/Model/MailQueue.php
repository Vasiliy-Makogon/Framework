<?php

namespace Krugozor\Framework\Module\MailQueue\Model;


use Krugozor\Framework\Model;
use Krugozor\Framework\Type\Datetime;

class MailQueue extends Model
{
    /**
     * Успешная отправка письма.
     * @var int
     */
    const STATUS_OK = 1;

    /**
     * Письмо в очереди.
     * @var int
     */
    const STATUS_WAIT = 0;

    /**
     * Письмо не отправлено и исключено из очереди.
     * @var int
     */
    const STATUS_FAIL = -1;

    protected static $model_attributes = [
        'id' => [
            'db_element' => false,
            'default_value' => 0,
            'validators' => [
                'Decimal' => ['signed' => false],
            ]
        ],

        'send_date' => [
            'type' => 'Krugozor\\Framework\\Type\\Datetime',
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'IsNotEmptyString' => [],
                'DateCorrect' => ['format' => Datetime::FORMAT_DATETIME],
            ]
        ],

        'template' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'IsNotEmptyString' => [],
                'StringLength' => ['start' => 0, 'stop' => 255],
            ]
        ],

        'to_email' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'IsNotEmptyString' => [],
                'StringLength' => ['start' => 0, 'stop' => 100],
                'Email' => [],
            ]
        ],

        'from_email' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'IsNotEmptyString' => [],
                'StringLength' => ['start' => 0, 'stop' => 100],
                'Email' => [],
            ]
        ],

        'cc_email' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'StringLength' => ['start' => 0, 'stop' => 100],
                'Email' => [],
            ]
        ],

        'reply_email' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'StringLength' => ['start' => 0, 'stop' => 100],
                'Email' => [],
            ]
        ],

        'header' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'IsNotEmptyString' => [],
                'StringLength' => ['start' => 0, 'stop' => 255],
            ]
        ],

        'mail_data' => [
            'db_element' => true,
            'default_value' => null,
            'validators' => [
                'IsNotEmptyString' => []
                // @todo: is array validator added
            ]
        ],

        'sended' => [
            'db_element' => true,
            'default_value' => 0,
            'validators' => [
                'IsNotEmptyString' => [],
                'Decimal' => ['signed' => true],
                'IntRange' => ['min' => -1, 'max' => 1],
            ]
        ],
    ];

    /**
     * @param array|string $data
     * @return string
     */
    public function _setMailData($data)
    {
        if (!is_string($data) && !is_array($data)) {
            throw new \InvalidArgumentException('Parameter $data must be a string or a array.');
        }

        return is_array($data) ? serialize($data) : unserialize($data);
    }
}