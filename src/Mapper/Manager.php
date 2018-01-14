<?php

/**
 * Объект-хранилище, содержащий инстанцированные mapper-объекты.
 *
 * Данный объект через метод Krugozor_Mapper_Manager->getMapper('ModuleName/ModelName') пораждает конкретный маппер
 * модели ModelName находящейся в модуле ModuleName.
 *
 * Объект Mapper_Manager доступен в Контроллере, Модели и в самих Мапперах.
 *
 * - В Контроллере доступ к Mapper_Manager напрямую запрещён, доступ
 * к конкретному Мапперу осуществляется через вызов метода контроллера
 * Krugozor_Controller->getMapper('ModuleName/ModelName');
 *
 * (Первое инстанцирование данного класса происходит в контроллере Krugozor_Controller->getMapper('ModuleName/ModelName'),
 * после чего Krugozor_Mapper_Manager передается во все создаваемые модели и мапперы.)
 *
 * - В Модели и Маппере доступ к Mapper_Manager осуществляется через метод
 * $this->getMapperManager(), доступ к конкретному Мапперу осуществляется через
 * $this->getMapperManager()->getMapper('ModuleName/ModelName');
 *
 * Объект СУБД доступен в Mapper_Manager, поэтому обращение в СУБД из маппера должно идти так:
 * $this->getMapperManager()->getDb() или $this->getDb()
 */
class Krugozor_Mapper_Manager
{
    /**
     * Коллекция инстанцированных мэпперов.
     *
     * @var array
     */
    private static $mappers = array();

    /**
     * @var \Krugozor\Database\Mysql\Mysql
     */
    private $db;

    /**
     * @param \Krugozor\Database\Mysql\Mysql $db
     */
    public function __construct(\Krugozor\Database\Mysql\Mysql $db)
    {
        $this->db = $db;
    }

    /**
     * Метод принимает строку вида `ModuleName/ModelMapperName`,
     * и возвращает объект мэппера, экземпляр класса
     * Krugozor_Module_ModuleName_Mapper_ModelMapperName
     *
     * @param string
     * @return Krugozor_Mapper_Common
     */
    public final function getMapper($path)
    {
        list($module, $model) = explode('/', $path);

        if (isset(self::$mappers[$module][$model])) {
            return self::$mappers[$module][$model];
        }

        $mapper_path = 'Krugozor_Module_' . $module . '_Mapper_' . $model;

        if (class_exists($mapper_path)) {
            return self::$mappers[$module][$model] = new $mapper_path($this);
        }
    }

    /**
     * Возвращает объект базы данных.
     *
     * @param void
     * @return \Krugozor\Database\Mysql\Mysql
     */
    public final function getDb(): \Krugozor\Database\Mysql\Mysql
    {
        return $this->db;
    }
}