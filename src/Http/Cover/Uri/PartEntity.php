<?php

/**
 * Объект-оболочка для имени модуля или контроллера.
 */
class Krugozor_Http_Cover_Uri_PartEntity
{
    /**
     * Имя модуля или контроллера в URI-стиле, т.е. в виде, когда
     * разные слова записаны через дефис. Например:
     * "frontend-registration", "backend-user-edit", "user" и т.д.
     *
     * @var string
     */
    private $uri_style = null;

    /**
     * Имя модуля или контроллера в CamelCase-стиле, т.е. в виде, когда
     * разные слова записаны слитно, а каждое новое слово (включая первое)
     * записано с Большой буквы. Например:
     * "FrontendRegistration", "BackendUserEdit", "User" и т.д.
     *
     * @var string
     */
    private $camel_case_style = null;

    /**
     * @param string строка в URI-стиле.
     * @return void
     */
    public function __construct($uri_style)
    {
        $this->uri_style = $uri_style;
    }

    /**
     * Возвращает имя модуля или контроллера в CamelCase-стиле.
     *
     * @param void
     * @return string
     */
    public function getCamelCaseStyle()
    {
        if ($this->camel_case_style === null) {
            $this->camel_case_style = Krugozor_Static_String::formatToCamelCaseStyle($this->uri_style);
        }

        return $this->camel_case_style;
    }

    /**
     * Возвращает имя модуля или контроллера в URI-стиле.
     *
     * @param void
     * @return string
     */
    public function getUriStyle()
    {
        return $this->uri_style;
    }
}