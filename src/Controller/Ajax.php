<?php

namespace Krugozor\Framework\Controller;

use Krugozor\Framework\Context;
use Krugozor\Framework\Controller;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\View;

/**
 * Контроллер, обрабатывающий Ajax-запросы.
 */
abstract class Ajax extends Controller
{
    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
        $this->default_view_class_name = 'Krugozor\Framework\View\Ajax';
        $this->getResponse()->setHeader(Response::HEADER_CONTENT_TYPE, 'application/json; charset=utf-8');
    }

    /**
     * @param null|string $template
     * @param null|string $view_class_name
     * @return View\Ajax
     */
    protected function getView(?string $template = null, ?string $view_class_name = null): View
    {
        // Для Ajax-запросов вывод отладочной информации закрыт.
        return parent::getView($template, $view_class_name)->setDebugInfoFlag(false);
    }
}