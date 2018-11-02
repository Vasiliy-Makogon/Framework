<?php

namespace Krugozor\Framework\Http;

use Krugozor\Framework\Http\Cover\Data\Cookie as CookieData;
use Krugozor\Framework\Http\Cover\Data\Get as GetData;
use Krugozor\Framework\Http\Cover\Data\Post as PostData;
use Krugozor\Framework\Http\Cover\Data\Request as RequestData;
use Krugozor\Framework\Http\Cover\Uri\CanonicalRequestUri;
use Krugozor\Framework\Http\Cover\Uri\PartEntity;
use Krugozor\Framework\Http\Cover\Uri\RequestUri;
use Krugozor\Framework\Singleton;
use Krugozor\Framework\Statical\Numeric;
use Krugozor\Framework\Statical\StaticType;

class Request implements Singleton
{
    const SANITIZE_STRING = 'string';
    const SANITIZE_BOOL = 'bool';
    const SANITIZE_ARRAY = 'array';
    const SANITIZE_INT = 'int';

    /**
     * @var Request
     */
    private static $instance;

    /**
     * @var RequestData
     */
    private $request_data;

    /**
     * @var GetData
     */
    private $get_data;

    /**
     * @var PostData
     */
    private $post_data;

    /**
     * @var CookieData
     */
    private $cookie_data;

    /**
     * Объект-обертка над запрошенным URI, который представляет собой
     * канонический адрес документа без учёта параметров в QUERY STRING.
     *
     * @var CanonicalRequestUri
     */
    private $canonicalRequestUri;

    /**
     * Объект-оболочка над запрошенным $_SERVER['REQUEST_URI'].
     *
     * @var RequestUri
     */
    private $requestUri;

    /**
     * Объект-обертка над именем модуля.
     *
     * @var PartEntity
     */
    private $module_name;

    /**
     * Объект-обертка над именем контроллера.
     *
     * @var PartEntity
     */
    private $controller_name;

    /**
     * @return Request
     */
    public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->request_data = new RequestData($_REQUEST);
        $this->post_data = new PostData($_POST);
        $this->get_data = new GetData($_GET);
        $this->cookie_data = new CookieData($_COOKIE);
    }

    /**
     * Данный метод является профилактикой ошибки присваивания значения
     * объекту напрямую, минуя вызов функций получения ссылок на
     * соответсвующие объекты хранилищ GPCR.
     *
     * @param string $key
     * @param $value
     */
    public function __set(string $key, $value)
    {
        throw new \InvalidArgumentException('Попытка присвоить значение в Request минуя вызовы GPCR');
    }

    /**
     * Получает ссылку на хранилище GET.
     *
     * @param null|string $key ключ возвращаемого значения
     * @param null|string $type приведение к типу
     * @return GetData|mixed
     */
    public function getGet(?string $key = null, ?string $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->get_data->$key
                : self::sanitizeValue($this->get_data->$key, $type);
        }

        return $this->get_data;
    }

    /**
     * Получает ссылку на хранилище POST.
     *
     * @param null|string $key ключ возвращаемого значения
     * @param null|string $type приведение к типу
     * @return PostData|mixed
     */
    public function getPost(?string $key = null, ?string $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->post_data->$key
                : self::sanitizeValue($this->post_data->$key, $type);
        }

        return $this->post_data;
    }

    /**
     * Получает ссылку на хранилище COOKIE.
     *
     * @param null|string $key ключ возвращаемого значения
     * @param null|string $type приведение к типу
     * @return CookieData|mixed
     */
    public function getCookie(?string $key = null, ?string $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->cookie_data->$key
                : self::sanitizeValue($this->cookie_data->$key, $type);
        }

        return $this->cookie_data;
    }

    /**
     * Получает ссылку на хранилище REQUEST.
     *
     * @param null|string $key ключ возвращаемого значения
     * @param null|string $type приведение к типу
     * @return RequestData|mixed
     */
    public function getRequest(?string $key = null, ?string $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->request_data->$key
                : self::sanitizeValue($this->request_data->$key, $type);
        }

        return $this->request_data;
    }

    /**
     * Возвращает true, если текущий запрос POST,
     * false в противном случае.
     *
     * @return bool
     */
    public static function isPost(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Возвращает true, если текущий запрос GET,
     * false в противном случае.
     *
     * @return bool
     */
    public static function isGet(): bool
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Возвращает true, если дата (обычно документа) $data является устаревшей
     * по отношению к HTTP заголовку If-Modified-Since.
     *
     * @param $date \DateTime
     * @return bool
     */
    public static function IfModifiedSince(\DateTime $date): bool
    {
        if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
            $if_modified_since = strtotime(substr($_SERVER['HTTP_IF_MODIFIED_SINCE'], 5));

            if ($if_modified_since && $if_modified_since >= $date->getTimestamp()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return PartEntity
     */
    public function getModuleName(): PartEntity
    {
        return $this->module_name;
    }

    /**
     * @param PartEntity $name URL модуля в оболочке PartEntity
     * @return Request
     */
    public function setModuleName(PartEntity $name): self
    {
        if ($this->module_name === null) {
            $this->module_name = $name;
        }

        return $this;
    }

    /**
     * @return PartEntity
     */
    public function getControllerName(): PartEntity
    {
        return $this->controller_name;
    }

    /**
     * @param PartEntity $name URL контроллера в оболочке PartEntity
     * @return Request
     */
    public function setControllerName(PartEntity $name): self
    {
        if ($this->controller_name === null) {
            $this->controller_name = $name;
        }

        return $this;
    }

    /**
     * @return CanonicalRequestUri
     */
    public function getCanonicalRequestUri(): CanonicalRequestUri
    {
        return $this->canonicalRequestUri;
    }

    /**
     * @param CanonicalRequestUri $canonicalRequestUri REQUEST_URI в оболочке CanonicalRequestUri
     * @return Request
     */
    public function setCanonicalRequestUri(CanonicalRequestUri $canonicalRequestUri): self
    {
        if ($this->canonicalRequestUri === null) {
            $this->canonicalRequestUri = $canonicalRequestUri;
        }

        return $this;
    }

    /**
     * @return RequestUri
     */
    public function getRequestUri()
    {
        return $this->requestUri;
    }

    /**
     * @param RequestUri $requestUri REQUEST_URI в оболочке RequestUri
     * @return Request
     */
    public function setRequestUri(RequestUri $requestUri): self
    {
        if ($this->requestUri === null) {
            $this->requestUri = $requestUri;
        }

        return $this;
    }

    /**
     * Возвращает "виртуальный" путь для текущего контроллера.
     * Если данный метод вызовет контроллер Krugozor\Framework\Module\User\Controller\BackendEdit,
     * то метод вернет строку "User/BackendEdit".
     * Данный метод применяется для облегчения написания виртуальных путей к файлам интернационализации и шаблонам.
     *
     * @return string
     */
    public function getVirtualControllerPath(): string
    {
        return $this->getModuleName()->getCamelCaseStyle() . '/' . $this->getControllerName()->getCamelCaseStyle();
    }

    /**
     * Приведение к типу $type значение $value.
     *
     * @param mixed $value значение
     * @param string $type тип, к которому будет приведено значение
     * @return mixed
     */
    private static function sanitizeValue($value, string $type)
    {
        switch ($type) {
            case 'decimal':
            case self::SANITIZE_INT:
                $value = (string)$value;
                if (preg_match(Numeric::$pattern_sign_search, $value, $matches) !== 0) {
                    return $matches[0];
                }
                return 0;

            case self::SANITIZE_STRING:
                return (string)$value;

            case self::SANITIZE_BOOL:
            case 'boolean':
                return (bool)$value;

            case self::SANITIZE_ARRAY:
                return (array)$value;

            default:
                trigger_error(__METHOD__ . ': Недопустимый санитарный тип ' . $type);
                break;
        }

        return $value;
    }
}