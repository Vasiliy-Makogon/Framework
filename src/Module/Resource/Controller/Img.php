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
            throw new \RuntimeException("Не найден Anchor-файл `$anchor`");
        }

        $paths = [
            $anchor::getPath(),
            'resources',
            'css',
            $this->getRequest()->getRequest('file')
        ];
        $path = implode(DIRECTORY_SEPARATOR, $paths);

        if (!file_exists($path)) {
            $this->log("Call to undefined file by path $path");
            exit;
        }

        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mime_type = $finfo->file($path);

        print_r($mime_type);

        $this->getResponse()
            //->setHeader(Response::HEADER_CONTENT_TYPE, 'text/css; charset=utf-8')
            ->sendHeaders();

        echo (file_exists($path) ? file_get_contents($path) : '');
        exit;
    }
}