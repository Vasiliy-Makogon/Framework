<?php

class Krugozor_Module_Group_Service_DenormalizedAccesses
{
    /**
     * @var array
     */
    private $denormalized_accesses;

    /**
     * @param array $denormalized_accesses
     */
    public function __construct(array $denormalized_accesses)
    {
        $this->denormalized_accesses = $denormalized_accesses;
    }

    /**
     * Возвращает true, если у контроллера с ключом $controller_key модуля с ключом $module_key
     * стоит значение `1` как право доступа, false - в противном случае.
     *
     * @param string $module_key
     * @param string $controller_key
     * @return boolean
     */
    public function checkAccess($module_key, $controller_key)
    {
        return isset($this->denormalized_accesses[$module_key][$controller_key])
            ? (bool)$this->denormalized_accesses[$module_key][$controller_key]
            : false;
    }
}