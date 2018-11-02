<?php

namespace Krugozor\Framework\Module\Module\Model;

use Krugozor\Framework\Model;
use Krugozor\Framework\Validator\StringLength;

class Controller extends Model
{
    protected static $db_field_prefix = 'controller';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => true),
            )
        ),

        'id_module' => array(
            'db_element' => true,
            'db_field_name' => 'controller_id_module',
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => true),
            )
        ),

        'name' => array(
            'db_element' => true,
            'db_field_name' => 'controller_name',
            'validators' => array(
                'IsNotEmpty' => array(),
                'StringLength' => array(
                    'start' => 0,
                    'stop' => StringLength::VARCHAR_MAX_LENGTH
                ),
            )
        ),

        'key' => array(
            'db_element' => true,
            'db_field_name' => 'controller_key',
            'validators' => array(
                'IsNotEmpty' => array(),
                'CharPassword' => array(),
                'StringLength' => array('start' => 0, 'stop' => 150),
            )
        ),
    );

    /**
     * Ссылка на модуль, к которому принадлежит данный контроллер.
     *
     * @var Module
     */
    protected $module;

    /**
     * Возвращает модуль, ассоциируемый с данным контроллером.
     *
     * @return Module
     */
    public function getModule()
    {
        if (!$this->module) {
            $this->module = $this->getMapperManager()
                ->getMapper('Module/Module')
                ->findModelById($this->getIdModule());
        }

        return $this->module;
    }
}