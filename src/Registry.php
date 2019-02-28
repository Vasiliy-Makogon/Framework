<?php

namespace Krugozor\Framework;

use Krugozor\Cover\CoverArray;

class Registry extends CoverArray implements Singleton
{
    /**
     * @var Registry
     */
    protected static $instance;

    /**
     * Первый вызов данного регистра приходится с указанием ini-файла конфигурации.
     *
     * @param string|null $config_file_path путь к ini-файлу конфигурации
     * @return Registry
     */
    public static function getInstance(?string $config_file_path = null): self
    {
        if (self::$instance === null) {
            if ($config_file_path === null || !file_exists($config_file_path)) {
                throw new \RuntimeException("Configuration file not found at path `$config_file_path`");
            }

            $config = parse_ini_file($config_file_path, true);
            if (!$config) {
                throw new \RuntimeException("Unable to read configuration file by path `$config_file_path`");
            }

            self::$instance = new self($config);
        }

        return self::$instance;
    }
}