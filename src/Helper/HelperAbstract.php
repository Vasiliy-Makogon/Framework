<?php

namespace Krugozor\Framework\Helper;

/**
 * Базовый класс хэлперов, возвращающих сгенерированный частичный HTML-код.
 */
abstract class HelperAbstract
{
    /**
     * Возвращает сгенерированный HTML-код.
     *
     * @return string
     */
    abstract public function getHtml(): string;
}