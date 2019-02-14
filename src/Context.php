<?php

namespace Krugozor\Framework;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Http\Response;

/**
 * Объект-хранилище, содержащий все "звёздные" объекты системы.
 */
final class Context implements Singleton
{
    /**
     * @var Context
     */
    protected static $instance;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var \Krugozor\Database\Mysql\Mysql
     */
    protected $db;

    /**
     * @return Context
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
     * @return Request
     */
    final public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Принимает объект запроса.
     *
     * @param Request $request
     * @return Context
     */
    final public function setRequest(Request $request): self
    {
        if ($this->request === null) {
            $this->request = $request;
        }

        return $this;
    }

    /**
     * Возвращает объект ответа.
     *
     * @return Response
     */
    final public function getResponse(): Response
    {
        return $this->response;
    }

    /**
     * Принимает объект ответа.
     *
     * @param Response $response
     * @return Context
     */
    final public function setResponse(Response $response): self
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
     * @return Context
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