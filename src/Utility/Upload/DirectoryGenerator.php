<?php

class Krugozor_Utility_Upload_DirectoryGenerator
{
    private $file_name;

    private $depth = 3;

    public function __construct($file_name)
    {
        $this->file_name = (string)$file_name;

        if (!strlen($this->file_name)) {
            throw new Exception(__METHOD__ . ': Указан параметр нулевой длинны');
        }
    }

    /**
     * Создает директории (если они ещё не созданы) на основе имени файла
     * (например, d2d8f9c20083bd8483ac5d5526f923b9.jpeg) и возвращает путь.
     *
     * @param string $destinationDir директория назначния
     * @return string путь, например: i\700x600\d\2\d\
     */
    public function create($destinationDir)
    {
        $destinationDir = rtrim($destinationDir, '\/');

        for ($i = 0; $i < $this->depth; $i++) {
            $destinationDir .= DIRECTORY_SEPARATOR . $this->file_name[$i];

            if (!is_dir($destinationDir)) {
                if (!mkdir($destinationDir, 0775)) {
                    throw new RuntimeException(__METHOD__ . ': Не удалось создать директорию ' . $destinationDir);
                }
            }
        }

        return $destinationDir . DIRECTORY_SEPARATOR;
    }

    /**
     * На основе имени файла (например, d2d8f9c20083bd8483ac5d5526f923b9.jpeg)
     * возвращает путь к файлу для HTTP, вида /d/2/d/8/f/.
     *
     * @param void
     * @return string HTTP-путь к файлу
     */
    public function getHttpPath()
    {
        $destinationDir = '';

        for ($i = 0; $i < $this->depth; $i++) {
            $destinationDir .= '/' . $this->file_name[$i];
        }

        return $destinationDir . '/';
    }
}