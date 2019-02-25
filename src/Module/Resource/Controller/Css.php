<?php

namespace Krugozor\Framework\Module\Resource\Controller;

use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Module\Resource\Model\ResourceCss;
use Krugozor\Framework\Statical\Strings;

class Css extends Controller
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
            'css',
            $this->getRequest()->getRequest('file')
        ];
        $path = implode(DIRECTORY_SEPARATOR, $paths);

        try {
            $resource = new ResourceCss($path);
            $resource->checkMieType();

            $this->getResponse()
                ->unsetHeader('Last-Modified')
                ->unsetHeader('Expires')
                ->unsetHeader('Cache-Control')
                ->unsetHeader('Pragma');

            if (!Request::IfModifiedSince($resource->getModificationTime())) {
                return $this->getResponse()->setHttpStatusCode(304);
            }

            $this->getResponse()
                ->setHeader(Response::HEADER_CONTENT_TYPE, 'text/css; charset=utf-8')
                ->setHeader('Last-Modified', $resource->getModificationTime()->formatHttpDate())
                ->setHeader('Cache-Control', 'no-cache, must-revalidate');

            return $resource;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}