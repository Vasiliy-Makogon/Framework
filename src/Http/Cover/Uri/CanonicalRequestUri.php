<?php

namespace Krugozor\Framework\Http\Cover\Uri;

/**
 * Объект-оболочка над REQUEST_URI без QUERY_STRING (т.н. PHP_URL_PATH).
 * QUERY_STRING отсекается в Krugozor\Framework\Application
 */
class CanonicalRequestUri
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @param string строка запроса REQUEST_URI без QUERY_STRING.
     */
    public function __construct(string $uri)
    {
        $this->uri = self::stripNotifQS($uri);
    }

    /**
     * Возвращает строку REQUEST_URI адреса.
     *
     * @param bool $full_path true - возвразщать полный путь (со схемой), false - относительный.
     * @return string
     */
    public function getSimpleUriValue(bool $full_path = false): string
    {
        return $full_path ? $this->getHttpScheme() . $_SERVER['HTTP_HOST'] . $this->uri : $this->uri;
    }

    /**
     * Возвращает urlencode строку REQUEST_URI адреса.
     *
     * @param bool $full_path true - возвразщать полный путь (со схемой), false - относительный.
     * @return string
     */
    public function getUrlencodeUriValue(bool $full_path = false): string
    {
        return urlencode($this->getSimpleUriValue($full_path));
    }

    /**
     * Возвращает htmlspecialchars строку REQUEST_URI адреса.
     *
     * @param bool $full_path true - возвразщать полный путь (со схемой), false - относительный.
     * @return string
     */
    public function getEscapeUriValue(bool $full_path = false): string
    {
        return htmlspecialchars($this->getSimpleUriValue($full_path), ENT_QUOTES, 'UTF-8');
    }

    /**
     * Функция вырезает из строки URL параметр &notif=
     *
     * @param string
     * @return string
     */
    protected static function stripNotifQS(string $in): string
    {
        return preg_replace('/(&|%26|\?|%3F)notif(=|%3D)[0-9]+/', '', $in);
    }

    /**
     * Получение текущей схемы запроса.
     *
     * @return string
     */
    protected function getHttpScheme(): string
    {
        return
            isset($_SERVER['HTTP_SCHEME'])
            ? $_SERVER['HTTP_SCHEME']
            : (
                ((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') || 443 == $_SERVER['SERVER_PORT'])
                    ? 'https://'
                    : 'http://'
            );
    }
}