<?php

namespace Krugozor\Framework\Module\Resource\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Statical\Strings;

class Js extends Controller
{
    public function run()
    {
        $paths = [
            dirname(dirname(dirname(__DIR__))),
            'Module',
            Strings::formatToCamelCaseStyle($this->getRequest()->getRequest('module')),
            'resources',
            'js',
            $this->getRequest()->getRequest('file')
        ];
        $path = implode(DIRECTORY_SEPARATOR, $paths);

        $this->getResponse()
            ->setHeader(Response::HEADER_CONTENT_TYPE, 'application/x-javascript; charset=utf-8')
            ->sendHeaders();

        echo file_exists($path)
            ? file_get_contents($path)
            : 'console.log("Not found '. addslashes($path) . '");';
        exit;
    }
}