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

        print_r($mime_type);

        if (!in_array($mime_type, ['text/plain'])) {
            throw new \RuntimeException('Call not js-resource file by path ' . $this->path);
        }
    }
}