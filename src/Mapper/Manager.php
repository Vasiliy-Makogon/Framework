<?php

namespace Krugozor\Framework\Mapper;

use Krugozor\Framework\Mapper;

/**
 * Объект-хранилище, содержащий инстанцированные mapper-объекты.
 *
 * Данный объект через метод Manager::getMapper('ModuleName/ModelName') пораждает конкретный
 * маппер модели ModelName находящейся в модуле ModuleName.
 *
 *
 * Объект Manager доступен в Контроллере, Модели и в самих Мапперах.
 *
 * - В Контроллере доступ к Manager напрямую запрещён, доступ
 * к конкретному Мапперу осуществляется через вызов метода контроллера
 * Controller::getMapper('ModuleName/ModelName');
 *
 * Первое инстанцирование данного класса происходит в Controller::getMapper(),
 * после чего Manager передается во все создаваемые модели и мапперы, т.к. инстанс всех
 * мапперов происходит из Контроллеров, а всех моделей - из Мапперов.
 *
 * - В Модели и Маппере доступ к Manager осуществляется через метод
 * $this->getMapperManager(), доступ к конкретному Мапперу осуществляется через
 * $this->getMapperManager()->getMapper('ModuleName/ModelName');
 *
 * Объект СУБД доступен в Manager, поэтому обращение в СУБД из маппера
 * должно идти так: Manager->getDb()
 */
class Manager
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
     * Krugozor\Framework\Module\ModuleName\Mapper\ModelMapperName
     *
     * @param string
     * @return Mapper
     */
    public final function getMapper($path): Mapper
    {
        list($module, $model) = explode('/', $path);

        if (isset(self::$mappers[$module][$model])) {
            return self::$mappers[$module][$model];
        }

        $mapper_path = 'Krugozor\\Framework\\Module\\' . $module . '\\Mapper\\' . $model;
        try {
            $o = new $mapper_path($this);
            return self::$mappers[$module][$model] = $o;
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Возвращает объект базы данных.
     *
     * @return \Krugozor\Database\Mysql\Mysql
     */
    public final function getDb(): \Krugozor\Database\Mysql\Mysql
    {
        return $this->db;
    }
}