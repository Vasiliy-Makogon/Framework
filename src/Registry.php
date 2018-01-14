<?php

class Krugozor_Registry extends Krugozor_Cover_Array implements Krugozor_Interface_Singleton
{
    protected static $instance;

    /**
     * Первый вызов данного регистра приходится с указанием ini-файла конфигурации.
     *
     * @param string $config_file_path путь к ini-файлу конфигурации
     * @throws RuntimeException
     * @return Krugozor_Registry
     */
    public static function getInstance(string $config_file_path = null): self
    {
        if (self::$instance === null) {
            if (!file_exists($config_file_path)) {
                throw new RuntimeException('Не найден файл конфигурации ' . $config_file_path);
            }

            $config = parse_ini_file($config_file_path, true);

            if (!$config) {
                throw new RuntimeException('Невозможно прочитать файл конфигурации ' . $config_file_path);
            }

            self::$instance = new self($config);
        }

        return self::$instance;
    }
}