<?php

namespace Krugozor\Framework\Module\Resource\Model;

class ResourceJs extends Resource
{
    /**
     * Производит проверку mime-типа ресурса на изображение.
     * @return null|string
     */
    public function checkMieType()
    {
        $mime_type = $this->getMimeType();

        if (!in_array($mime_type, ['text/plain', 'text/html'])) {
            throw new \RuntimeException('Call not js-resource file by path ' . $this->path);
        }
    }
}