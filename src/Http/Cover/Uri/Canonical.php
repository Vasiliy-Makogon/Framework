<?php

/**
 * Объект-оболочка над REQUEST_URI без QUERY_STRING (т.н. PHP_URL_PATH).
 * QUERY_STRING отсекается в Krugozor_Application
 */
class Krugozor_Http_Cover_Uri_Canonical
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @param string строка URI.
     * @return void
     */
    public function __construct($uri)
    {
        $this->uri = self::stripNotifQS($uri);
    }

    /**
     * @param string $full_path
     * @return string
     */
    public function getSimpleUriValue($full_path = false)
    {
        return $full_path ? 'http://' . $_SERVER['HTTP_HOST'] . $this->uri : $this->uri;
    }

    /**
     * @param string $full_path
     * @return string
     */
    public function getUrlencodeUriValue($full_path = false)
    {
        return urlencode($this->getSimpleUriValue($full_path));
    }

    /**
     * @param string $full_path
     * @return string
     */
    public function getEscapeUriValue($full_path = false)
    {
        return htmlspecialchars($this->getSimpleUriValue($full_path), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Функция вырезает из строки URL параметр &notif=
     *
     * @param string
     * @return string
     */
    protected static function stripNotifQS($in)
    {
        return preg_replace('/(&|%26|\?|%3F)notif(=|%3D)[0-9]+/', '', $in);
    }
}