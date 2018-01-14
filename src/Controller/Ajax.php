<?php

/**
 * Контроллер, обрабатывающий Ajax-запросы.
 */
abstract class Krugozor_Controller_Ajax extends Krugozor_Controller
{
    protected $default_view_class_name = 'Krugozor_View_Ajax';

    public function __construct(Krugozor_Context $context)
    {
        parent::__construct($context);

        $this->getResponse()->setHeader(Krugozor_Http_Response::HEADER_CONTENT_TYPE, 'application/json; charset=utf-8');
    }

    /**
     * @see Krugozor_Controller::getView()
     */
    protected function getView($template = null, $view_class_name = null)
    {
        $view = parent::getView($template, $view_class_name);
        // Для Ajax-запросов вывод отладочной информации закрыт даже на для тестовой среды.
        $view->setDebugInfoFlag(false);

        return $view;
    }
}