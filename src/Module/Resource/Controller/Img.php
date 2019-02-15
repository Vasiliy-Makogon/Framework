<?php

namespace Krugozor\Framework\Module\Resource\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Statical\Strings;

class Img extends Controller
{
    public function run()
    {
        $anchor = 'Krugozor\\Framework\\Module\\' .
                  Strings::formatToCamelCaseStyle($this->getRequest()->getRequest('module')) .
                  '\\Anchor';
        if (!class_exists($anchor)) {
            throw new \RuntimeException("Not found Anchor-file at `$anchor`");
        }

        $paths = [
            $anchor::getPath(),
            'resources',
            'img',
            $this->getRequest()->getRequest('file')
        ];
        $path = implode(DIRECTORY_SEPARATOR, $paths);

        if (!file_exists($path)) {
            $this->log("Call to undefined file by path $path");
            exit;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($path);

        switch ($mime_type) {
            case 'image/png':
                $this->getResponse()->setHeader(Response::HEADER_CONTENT_TYPE, 'image/png');
                break;
            case 'image/jpeg':
                $this->getResponse()->setHeader(Response::HEADER_CONTENT_TYPE, 'image/jpeg');
                break;
            case 'image/gif':
                $this->getResponse()->setHeader(Response::HEADER_CONTENT_TYPE, 'image/gif');
                break;
            case 'image/x-icon':
                $this->getResponse()->setHeader(Response::HEADER_CONTENT_TYPE, 'image/x-icon');
                break;
            default:
                exit;
        }

        $this->getResponse()->sendHeaders();

        echo (file_exists($path) ? file_get_contents($path) : '');
        exit;
    }
}