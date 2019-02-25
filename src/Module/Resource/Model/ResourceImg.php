<?php

namespace Krugozor\Framework\Module\Resource\Model;

class ResourceImg extends Resource
{
    /**
     * Производит проверку mime-типа ресурса на изображение.
     * @return null|string
     */
    public function checkMieType()
    {
        $mime_type = $this->getMimeType();

        if (!in_array($mime_type, ['image/png', 'image/jpeg', 'image/gif', 'image/x-icon'])) {
            throw new \RuntimeException('Call not image resource file by path ' . $this->path);
        }
    }
}