<?php

namespace Krugozor\Framework\Module\Resource\Model;

class ResourcePdf extends Resource
{
    /**
     * Производит проверку mime-типа ресурса на PDF.
     * @return null|string
     */
    public function checkMieType()
    {
        $mime_type = $this->getMimeType();

        if (!in_array($mime_type, ['application/pdf'])) {
            throw new \RuntimeException('Call not pdf-resource file by path ' . $this->path);
        }
    }
}