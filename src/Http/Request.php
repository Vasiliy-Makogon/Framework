<?php

class Krugozor_Http_Request implements Krugozor_Interface_Singleton
{
    /**
     * @var Krugozor_Http_Request
     */
    private static $instance;

    /**
     * @var Krugozor_Http_Cover_Data_Request
     */
    private $request_data;

    /**
     * @var Krugozor_Http_Cover_Data_Get
     */
    private $get_data;

    /**
     * @var Krugozor_Http_Cover_Data_Post
     */
    private $post_data;

    /**
     * @var Krugozor_Http_Cover_Data_Cookie
     */
    private $cookie_data;

    /**
     * Объект-обертка над запрошенным URI, который представляет собой
     * канонический адрес документа без учёта параметров в QUERY STRING.
     *
     * @var Krugozor_Http_Cover_Uri_Canonical
     */
    private $uri;

    /**
     * Объект-оболочка над запрошенным $_SERVER['REQUEST_URI'].
     *
     * @var Krugozor_Http_Cover_Uri_Request
     */
    private $request_uri;

    /**
     * Объект-обертка над именем модуля.
     *
     * @var Krugozor_Http_Cover_Uri_PartEntity
     */
    private $module_name;

    /**
     * Объект-обертка над именем контроллера.
     *
     * @var Krugozor_Http_Cover_Uri_PartEntity
     */
    private $controller_name;

    /**
     * @return Krugozor_Http_Request
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    private function __construct()
    {
        $this->request_data = new Krugozor_Http_Cover_Data_Request($_REQUEST);
        $this->post_data = new Krugozor_Http_Cover_Data_Post($_POST);
        $this->get_data = new Krugozor_Http_Cover_Data_Get($_GET);
        $this->cookie_data = new Krugozor_Http_Cover_Data_Cookie($_COOKIE);
    }

    /**
     * Данный метод является профилактикой ошибки присваивания значения
     * объекту напрямую, минуя вызов функций получения ссылок на
     * соответсвующие объекты хранилищ GPCR.
     *
     * @see __set()
     */
    public function __set($key, $value)
    {
        throw new InvalidArgumentException('Попытка присвоить значение в Request минуя вызовы GPCR');
    }

    /**
     * Получает ссылку на хранилище GET.
     *
     * @param $key ключ возвращаемого значения
     * @param $type приведение к типу
     * @return Krugozor_Http_Cover_Data_Get
     */
    public function getGet($key = null, $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->get_data->$key
                : Krugozor_Static_Type::sanitizeValue($this->get_data->$key, $type);
        }

        return $this->get_data;
    }

    /**
     * Получает ссылку на хранилище POST.
     *
     * @param $key ключ возвращаемого значения
     * @param $type приведение к типу
     * @return Krugozor_Http_Cover_Data_Post
     */
    public function getPost($key = null, $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->post_data->$key
                : Krugozor_Static_Type::sanitizeValue($this->post_data->$key, $type);
        }

        return $this->post_data;
    }

    /**
     * Получает ссылку на хранилище COOKIE.
     *
     * @param $key ключ возвращаемого значения
     * @param $type приведение к типу
     * @return Krugozor_Http_Cover_Data_Cookie
     */
    public function getCookie($key = null, $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->cookie_data->$key
                : Krugozor_Static_Type::sanitizeValue($this->cookie_data->$key, $type);
        }

        return $this->cookie_data;
    }

    /**
     * Получает ссылку на хранилище REQUEST.
     *
     * @param $key ключ возвращаемого значения
     * @param $type приведение к типу
     * @return Krugozor_Http_Cover_Data_Request
     */
    public function getRequest($key = null, $type = null)
    {
        if ($key !== null) {
            return $type === null
                ? $this->request_data->$key
                : Krugozor_Static_Type::sanitizeValue($this->request_data->$key, $type);
        }

        return $this->request_data;
    }

    /**
     * Возвращает TRUE, если текущий запрос POST,
     * FALSE в противном случае.
     *
     * @param void
     * @return bool
     */
    public static function isPost()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST';
    }

    /**
     * Возвращает TRUE, если текущий запрос GET,
     * FALSE в противном случае.
     *
     * @param void
     * @return boolean
     */
    public static function isGet()
    {
        return $_SERVER['REQUEST_METHOD'] === 'GET';
    }

    /**
     * Возвращает TRUE, если дата (обычно документа) $data является устаревшей
     * по отношению к HTTP заголовку If-Modified-Since.
     *
     * @param $date DateTime
     * @return boolean
     */
    public static function IfModifiedSince(DateTime $date)
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
     * @return Krugozor_Http_Cover_Uri_PartEntity
     */
    public function getModuleName()
    {
        return $this->module_name;
    }

    /**
     * @param Krugozor_Http_Cover_Uri_PartEntity $name
     */
    public function setModuleName(Krugozor_Http_Cover_Uri_PartEntity $name)
    {
        if ($this->module_name === null) {
            $this->module_name = $name;
        }
    }

    /**
     * @return Krugozor_Http_Cover_Uri_PartEntity
     */
    public function getControllerName()
    {
        return $this->controller_name;
    }

    /**
     * @param Krugozor_Http_Cover_Uri_PartEntity $name
     */
    public function setControllerName(Krugozor_Http_Cover_Uri_PartEntity $name)
    {
        if ($this->controller_name === null) {
            $this->controller_name = $name;
        }
    }

    /**
     * @return Krugozor_Http_Cover_Uri_Canonical
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * @param Krugozor_Http_Cover_Uri_Canonical
     */
    public function setUri(Krugozor_Http_Cover_Uri_Canonical $uri)
    {
        if ($this->uri === null) {
            $this->uri = $uri;
        }
    }

    /**
     * @return Krugozor_Http_Cover_Uri_Request
     */
    public function getRequestUri()
    {
        return $this->request_uri;
    }

    /**
     * @param Krugozor_Http_Cover_Uri_Request
     */
    public function setRequestUri(Krugozor_Http_Cover_Uri_Request $request_uri)
    {
        if ($this->request_uri === null) {
            $this->request_uri = $request_uri;
        }
    }

    /**
     * Возвращает "виртуальный" путь для текущего контроллера.
     * Если данный метод вызовет контроллер Module_User_Controller_BackendEdit,
     * то метод вернет строку "User/BackendEdit".
     * Данный метод применяется для облегчения написания виртуальных путей к
     * файлам интернационализации и шаблонам.
     *
     * @param void
     * @return string
     */
    public function getVirtualControllerPath()
    {
        return $this->getModuleName()->getCamelCaseStyle() . '/' . $this->getControllerName()->getCamelCaseStyle();
    }
}