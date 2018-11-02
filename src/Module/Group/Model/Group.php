<?php

namespace Krugozor\Framework\Module\Group\Model;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Model;
use Krugozor\Framework\Module\Group\Service\DenormalizedAccesses;

class Group extends Model
{
    protected static $db_field_prefix = 'group';

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
            'db_field_name' => 'group_name',
            'validators' => array(
                'IsNotEmpty' => array(),
            )
        ),

        'active' => array(
            'db_element' => true,
            'default_value' => 1,
            'db_field_name' => 'group_active',
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => true),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'alias' => array(
            'db_element' => true,
            'db_field_name' => 'group_alias',
            'validators' => array(
                'IsNotEmpty' => array(),
                'CharPassword' => array(),
            )
        ),

        // Сериализованный массив прав доступа группы к модулям системы.
        // Данное свойство записывается исключительно в момент сохранения данных группы
        // в контроллере \Krugozor\Framework\Module\Group\Controller\BackendEdit
        'access' => array(
            'db_element' => true,
            'default_value' => 'a:0:{}',
            'db_field_name' => 'group_access',
            'validators' => array()
        ),
    );

    /**
     * Коллекция объектов доступа группы (Access)
     * к контроллерам системы. Заполняется при POST-запросе.
     *
     * @var CoverArray
     */
    protected $accesses;

    /**
     * @var DenormalizedAccesses
     */
    protected $denormalized_accesses;

    /**
     * Денормализует права доступа группы после сериализации или достает их из базы, после чего
     * возвращает объект DenormalizedAccesses с помощью которого можно проверить право группы
     * на доступ к конкретному контроллеру.
     *
     * Права хранятся в виде структуры
     *
     *    [ModuleKey] => Array (
     *      [ContollerKey] => int access
     *      [...]
     *    )
     *
     * @return DenormalizedAccesses
     */
    public function getDenormalizedAccesses()
    {
        // lazy load еще не срабатывал
        if ($this->denormalized_accesses === null) {
            $this->denormalized_accesses = [];

            // в поле `access` обнаружены сериализованные права доступа, получаем их
            if (!empty($this->data['access'])) {
                $this->denormalized_accesses = unserialize($this->data['access']);
            } else {
                // прав доступа в поле `access` не найдено, делаем запрос на их получение
                $this->denormalized_accesses = $this->getMapperManager()->getMapper('Group/Access')
                    ->getGroupAccessByIdWithControllerNames($this->getId())
                    ->getDataAsArray();
            }

            $this->denormalized_accesses = new DenormalizedAccesses($this->denormalized_accesses);
        }

        return $this->denormalized_accesses;
    }

    /**
     * @param array|CoverArray $data
     * @param array $excluded_keys
     * @return Model
     */
    public function setData($data, array $excluded_keys = array()): Model
    {
        parent::setData($data, $excluded_keys);

        // При создании/редактировании группы из административной части.
        if (!empty($data['accesses'])) {
            if (!$this->accesses) {
                $this->accesses = new CoverArray();
            }

            foreach ($data['accesses'] as $id_module => $access_data) {
                foreach ($access_data as $id_controller => $access_value) {
                    $access = $this->getMapperManager()
                        ->getMapper('Group/Access')
                        ->createModel()
                        ->setIdGroup($this->getId())
                        ->setIdController($id_controller)
                        ->setAccess($access_value);

                    $this->accesses->append($access);
                }
            }
        }

        return $this;
    }

    /**
     * Возвращает коллецию объектов доступа.
     *
     * @return CoverArray
     */
    public function getAccesses()
    {
        if (!$this->accesses) {
            $this->accesses = $this->findAccesses();
        }

        return $this->accesses;
    }

    /**
     * Ищет и возвращает коллецию объектов доступа.
     * Lazy Load.
     *
     * @return CoverArray
     */
    protected function findAccesses()
    {
        if (!$this->accesses) {
            $this->accesses = new CoverArray();

            foreach ($this->getMapperManager()->getMapper('Group/Access')->findByGroup($this) as $access) {
                $this->accesses->append($access);
            }
        }

        return $this->accesses;
    }
}