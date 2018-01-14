<?php

/**
 * Объект-хранилище, содержащий все "звёздные" объекты системы.
 */
final class Krugozor_Context implements Krugozor_Interface_Singleton
{
    /**
     * @var Krugozor_Context
     */
    protected static $instance;

    /**
     * @var Krugozor_Http_Request
     */
    protected $request;

    /**
     * @var Krugozor_Http_Response
     */
    protected $response;

    /**
     * @var \Krugozor\Database\Mysql\Mysql
     */
    protected $db;

    /**
     * @static
     * @return Krugozor_Context
     */
    final public static function getInstance(): self
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Возвращает объект запроса.
     *
     * @return Krugozor_Http_Request
     */
    final public function getRequest(): Krugozor_Http_Request
    {
        return $this->request;
    }

    /**
     * Принимает объект запроса.
     *
     * @param Krugozor_Http_Request $request
     * @return Krugozor_Context
     */
    final public function setRequest(Krugozor_Http_Request $request): self
    {
        if ($this->request === null) {
            $this->request = $request;
        }

        return $this;
    }

    /**
     * Возвращает объект ответа.
     *
     * @return Krugozor_Http_Response
     */
    final public function getResponse(): Krugozor_Http_Response
    {
        return $this->response;
    }

    /**
     * Принимает объект ответа.
     *
     * @param Krugozor_Http_Response $response
     * @return Krugozor_Context
     */
    final public function setResponse(Krugozor_Http_Response $response): self
    {
        if ($this->response === null) {
            $this->response = $response;
        }

        return $this;
    }

    /**
     * Возвращает объект СУБД.
     *
     * @return \Krugozor\Database\Mysql\Mysql
     */
    final public function getDatabase(): \Krugozor\Database\Mysql\Mysql
    {
        return $this->db;
    }

    /**
     * Принимает объект СУБД.
     *
     * @param \Krugozor\Database\Mysql\Mysql $db
     * @return Krugozor_Context
     */
    final public function setDatabase(\Krugozor\Database\Mysql\Mysql $db): self
    {
        if ($this->db === null) {
            $this->db = $db;
        }

        return $this;
    }

    private function __construct()
    {
    }
}