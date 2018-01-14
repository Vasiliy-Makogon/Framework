<?php

class Krugozor_Module_Group_Model_Group extends Krugozor_Model
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
                'IsNotEmpty' => array(),
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
        // в контроллере Krugozor_Module_Group_Controller_BackendEdit.
        'access' => array(
            'db_element' => true,
            'default_value' => 'a:0:{}',
            'db_field_name' => 'group_access',
            'validators' => array()
        ),
    );

    /**
     * Коллекция объектов доступа группы (Krugozor_Module_Group_Model_Access)
     * к контроллерам системы. Заполняется при POST-запросе.
     *
     * @var Krugozor_Cover_Array
     */
    protected $accesses;

    /**
     * @var Krugozor_Module_Group_Service_DenormalizedAccesses
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
     * @param void
     * @return Krugozor_Module_Group_Service_DenormalizedAccesses
     */
    public function getDenormalizedAccesses($module_key = null, $controller_key = null)
    {
        // lazy load еще не срабатывал
        if ($this->denormalized_accesses === null) {
            $this->denormalized_accesses = array();

            // в поле `access` обнаружены сериализованные права доступа, получаем их
            if (!empty($this->data['access'])) {
                $this->denormalized_accesses = unserialize($this->data['access']);
            } else {
                // прав доступа в поле `access` не найдено, делаем запрос на их получение
                $this->denormalized_accesses = $this->getMapperManager()->getMapper('Group/Access')
                    ->getGroupAccessByIdWithControllerNames($this->getId())
                    ->getDataAsArray();
            }

            $this->denormalized_accesses = new Krugozor_Module_Group_Service_DenormalizedAccesses($this->denormalized_accesses);
        }

        return $this->denormalized_accesses;
    }

    /**
     * @see Krugozor_Model::setData()
     */
    public function setData($data, array $excluded_keys = array())
    {
        parent::setData($data, $excluded_keys);

        // При создании/редактировании группы из административной части.
        if (!empty($data['accesses'])) {
            if (!$this->accesses) {
                $this->accesses = new Krugozor_Cover_Array();
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
     * @param void
     * @return Krugozor_Cover_Array
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
     * @param void
     * @return Krugozor_Cover_Array
     */
    protected function findAccesses()
    {
        if (!$this->accesses) {
            $this->accesses = new Krugozor_Cover_Array();

            foreach ($this->getMapperManager()->getMapper('Group/Access')->findByGroup($this) as $access) {
                $this->accesses->append($access);
            }
        }

        return $this->accesses;
    }
}