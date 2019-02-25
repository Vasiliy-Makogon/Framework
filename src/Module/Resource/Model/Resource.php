<?php

namespace Krugozor\Framework\Module\Resource\Model;

use Krugozor\Framework\Type\Datetime;

abstract class Resource
{
    /**
     * Путь к файлу ресурса.
     * @var string
     */
    protected $path;

    /**
     * Resource constructor.
     * @param string $path
     */
    public function __construct($path)
    {
        if (!file_exists($path)) {
            throw new \RuntimeException("Call to undefined resource file by path $path");
        }

        $this->path = $path;
    }

    /**
     * Возвращает время последнего изменения файла ресурса.
     * @return Datetime
     */
    public function getModificationTime()
    {
        return (new Datetime())->setTimestamp(filemtime($this->path));
    }

    /**
     * Возвращает mime type файла ресурса
     * @return null|string
     */
    public function getMimeType()
    {
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        return $finfo->file($this->path);
    }

    /**
     * Возвращает содержимое файла ресурса.
     * @return bool|string
     */
    public function getResourceContents()
    {
        return file_get_contents($this->path);
    }
}