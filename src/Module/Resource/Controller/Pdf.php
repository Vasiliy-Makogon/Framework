<?php

namespace Krugozor\Framework\Module\Resource\Controller;

use Krugozor\Framework\Application;
use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Module\Resource\Model\ResourcePdf;

class Pdf extends Controller
{
    public function run()
    {
        $paths = [
            Application::getAnchor($this->getRequest()->getRequest('module'))::getPath(),
            'resources',
            'pdf',
            $this->getRequest()->getRequest('file')
        ];
        $path = implode(DIRECTORY_SEPARATOR, $paths);

        try {
            $resource = new ResourcePdf($path);
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
                ->setHeader(Response::HEADER_CONTENT_TYPE, 'application/pdf')
                ->setHeader('Last-Modified', $resource->getModificationTime()->formatHttpDate())
                ->setHeader('Cache-Control', 'no-cache, must-revalidate');

            return $resource;
        } catch (\Exception $e) {
            throw $e;
        }
    }
}