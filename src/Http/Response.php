<?php

namespace Krugozor\Framework\Http;

use Krugozor\Framework\Singleton;

class Response implements Singleton
{
    /**
     * @var Response
     */
    private static $instance;

    /**
     * @var string
     */
    const HEADER_LOCATION = 'Location';

    /**
     * @var string
     */
    const HEADER_CONTENT_TYPE = 'Content-type';

    /**
     * @var string
     */
    const HEADER_CONTENT_LANGUAGE = 'Content-Language';

    /**
     * @var string
     */
    const HEADER_EXPIRES = 'Expires';

    /**
     * @var string
     */
    const HEADER_LAST_MODIFIED = 'Last-Modified';

    /**
     * @var string
     */
    const HEADER_CACHE_CONTROL = 'Cache-Control';

    /**
     * @var string
     */
    const HEADER_PRAGMA = 'Pragma';

    /**
     * Код состояния HTTP,
     * например: HTTP/1.1 404 Not Found
     *
     * @var string
     */
    private $status = null;

    /**
     * Массив HTTP-заголовков вида `имя заголовка` => `значение`.
     *
     * @var array
     */
    private $headers = array();

    /**
     * Массив массивов информации о cookie.
     * Данные в массивах хранятся согласно последовательности
     * аргументов для функци setcookie.
     *
     * @var array
     */
    private $cookies = array();

    /**
     * Возвращает экземпляр ответа с HTTP-заголовками по-умолчанию.
     *
     * @return Response
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Устанавливает состояние ответа HTTP.
     *
     * @param int $code код состояния
     * @return Response
     */
    public function setHttpStatusCode($code = 200): self
    {
        switch ($code) {
            case 100:
                $text = 'Continue';
                break;
            case 101:
                $text = 'Switching Protocols';
                break;
            case 200:
                $text = 'OK';
                break;
            case 201:
                $text = 'Created';
                break;
            case 202:
                $text = 'Accepted';
                break;
            case 203:
                $text = 'Non-Authoritative Information';
                break;
            case 204:
                $text = 'No Content';
                break;
            case 205:
                $text = 'Reset Content';
                break;
            case 206:
                $text = 'Partial Content';
                break;
            case 300:
                $text = 'Multiple Choices';
                break;
            case 301:
                $text = 'Moved Permanently';
                break;
            case 302:
                $text = 'Moved Temporarily';
                break;
            case 303:
                $text = 'See Other';
                break;
            case 304:
                $text = 'Not Modified';
                break;
            case 305:
                $text = 'Use Proxy';
                break;
            case 400:
                $text = 'Bad Request';
                break;
            case 401:
                $text = 'Unauthorized';
                break;
            case 402:
                $text = 'Payment Required';
                break;
            case 403:
                $text = 'Forbidden';
                break;
            case 404:
                $text = 'Not Found';
                break;
            case 405:
                $text = 'Method Not Allowed';
                break;
            case 406:
                $text = 'Not Acceptable';
                break;
            case 407:
                $text = 'Proxy Authentication Required';
                break;
            case 408:
                $text = 'Request Time-out';
                break;
            case 409:
                $text = 'Conflict';
                break;
            case 410:
                $text = 'Gone';
                break;
            case 411:
                $text = 'Length Required';
                break;
            case 412:
                $text = 'Precondition Failed';
                break;
            case 413:
                $text = 'Request Entity Too Large';
                break;
            case 414:
                $text = 'Request-URI Too Large';
                break;
            case 415:
                $text = 'Unsupported Media Type';
                break;
            case 500:
                $text = 'Internal Server Error';
                break;
            case 501:
                $text = 'Not Implemented';
                break;
            case 502:
                $text = 'Bad Gateway';
                break;
            case 503:
                $text = 'Service Unavailable';
                break;
            case 504:
                $text = 'Gateway Time-out';
                break;
            case 505:
                $text = 'HTTP Version not supported';
                break;
            default:
                throw new \InvalidArgumentException('Unknown http status code: ' . $code);
                break;
        }

        $protocol = isset($_SERVER['SERVER_PROTOCOL']) ? $_SERVER['SERVER_PROTOCOL'] : 'HTTP/1.0';

        $this->status = $protocol . ' ' . $code . ' ' . $text;

        return $this;
    }

    /**
     * Устанавливает HTTP-заголовок ответа.
     *
     * @param string $name имя заголовка
     * @param string $value содержание заголовка
     * @return Response
     */
    public function setHeader(string $name, string $value): self
    {
        $this->headers[trim($name)] = trim($value);

        return $this;
    }

    /**
     * Разустанавливает HTTP-заголовок ответа.
     *
     * @param string $name имя заголовка
     * @return Response
     */
    public function unsetHeader(string $name): self
    {
        unset($this->headers[$name]);

        return $this;
    }

    /**
     * Отправляет HTTP-заголовки.
     *
     * @param bool true, если очищать хранилище заголовков, false в ином случае.
     * @return Response
     */
    public function sendHeaders(bool $clear = true): self
    {
        if ($this->status) {
            header($this->status);
        }

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        if ($clear) {
            $this->clearHeaders();
        }

        return $this;
    }

    /**
     * Возвращает массив установленных для отправки cookie
     * или одну cookie, если передано её имя.
     *
     * @param string имя cookie
     * @return array
     */
    public function getCookie(?string $name = null): array
    {
        if ($name !== null) {
            return isset($this->cookies[$name]) ? $this->cookies[$name] : null;
        }

        return $this->cookies;
    }

    /**
     * Устанавливает cookie во внутреннее представление класса.
     * API - аналог PHP-функции cookie.
     *
     * @see setcookie()
     * @param $name
     * @param null $value
     * @param int $expire
     * @param null $path
     * @param null $domain
     * @param bool $secure
     * @param bool $httponly
     * @return Response
     */
    public function setCookie($name, $value = null, $expire = 0, $path = null, $domain = null, $secure = false, $httponly = false): self
    {
        $this->cookies[$name] = array($value, $expire, $path, $domain, $secure, $httponly);

        return $this;
    }

    /**
     * Отправляет все установленные cookie.
     *
     * @return Response
     */
    public function sendCookie(): self
    {
        foreach ($this->cookies as $name => $data) {
            $args = array($name);

            foreach ($data as $value) {
                if ($value !== null) {
                    $args[] = $value;
                }
            }

            call_user_func_array('setcookie', $args);
        }

        $this->cookies = array();

        return $this;
    }

    /**
     * Очищает заголовки ответа.
     *
     * @return Response
     */
    public function clearHeaders(): self
    {
        $this->status = null;
        $this->headers = array();

        return $this;
    }

    /**
     * Очищает куки.
     *
     * @return Response
     */
    public function clearCookie(): self
    {
        $this->cookies = array();

        return $this;
    }
}