<?php

namespace Krugozor\Framework\Module\Resource\Model;

class ResourceCss extends Resource
{
    /**
     * Производит проверку mime-типа ресурса на изображение.
     * @return null|string
     */
    public function checkMieType()
    {
        $mime_type = $this->getMimeType();

        if (!in_array($mime_type, ['text/plain', 'text/html'])) {
            throw new \RuntimeException('Call not css-resource file by path ' . $this->path);
        }
    }
}