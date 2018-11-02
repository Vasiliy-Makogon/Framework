<?php

namespace Krugozor\Framework\Module\Module\Model;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Model;

class Module extends Model
{
    protected static $db_field_prefix = 'module';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => true),
            )
        ),

        'name' => array(
            'db_element' => true,
            'db_field_name' => 'module_name',
            'validators' => array(
                'IsNotEmpty' => array(),
                'StringLength' => array('start' => 0, 'stop' => 50),
            )
        ),

        'key' => array(
            'db_element' => true,
            'db_field_name' => 'module_key',
            'validators' => array(
                'IsNotEmpty' => array(),
                'CharPassword' => array(),
                'StringLength' => array('start' => 0, 'stop' => 30),
            )
        ),
    );

    /**
     * Коллекция контроллеров модуля.
     *
     * @var CoverArray
     */
    protected $controllers;

    /**
     * Возвращает коллекцию, содержащую все контроллеры, принадлежащие данному модулю.
     * Lazy Load.
     *
     * @return CoverArray
     */
    public function getControllers(): CoverArray
    {
        if (!$this->controllers) {
            $this->controllers = $this->getMapperManager()->getMapper('Module/Controller')->findControllersListByModule($this);
        }

        return $this->controllers;
    }
}