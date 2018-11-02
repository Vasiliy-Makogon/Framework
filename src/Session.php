<?php

namespace Krugozor\Framework;

use Krugozor\Cover\CoverArray;

class Session implements Singleton
{
    /**
     * @var Session
     */
    private static $instance;

    /**
     * Хранилище данных, передаваемых через магические методы __set и __get.
     *
     * @var CoverArray
     */
    protected $data;

    /**
     * Имя сессии.
     *
     * @var string
     */
    private $session_name;

    /**
     * ID сессии.
     *
     * @var string
     */
    private $session_id;

    /**
     * Инициализация сессии.
     *
     * @param null|string $session_name имя сессии
     * @param null|string $session_id идентификатор сессии
     * @return Session
     */
    public static function getInstance(?string $session_name = null, ?string $session_id = null): self
    {
        if (self::$instance === null) {
            self::$instance = new self($session_name, $session_id);
        }

        return self::$instance;
    }

    /**
     * Возвращает элемент из хранилища данных $this->data.
     *
     * @param string $key
     * @return mixed
     */
    public function __get(string $key)
    {
        return $this->data->$key;
    }

    /**
     * Добавляет новый элемент в хранилище данных $this->data.
     *
     * @param string $key ключ
     * @param mixed $value значение
     */
    public function __set(string $key, $value)
    {
        $this->data->$key = $value;
    }

    /**
     * Уничтожает сессию.
     */
    public function destroy()
    {
        $_SESSION = [];
        $this->data = null;

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(), '', time() - 42000,
                $params['path'], $params['domain'], $params['secure'], $params['httponly']
            );
        }

        session_destroy();
    }

    /**
     * Возвращает имя сессии.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->session_name;
    }

    /**
     * Возвращает идентификатор сессии.
     *
     * @return string
     */
    public function getId(): string
    {
        return $this->session_id;
    }

    public function __destruct()
    {
        $this->save();
    }

    /**
     * Стартует сессию. Устанавливает имя сесии $session_name, если оно определено
     * и стандартное имя PHPSESSID в обратном случае.
     *
     * @param null|string $session_name имя сессии
     * @param null|string $session_id идентификатор сессии
     */
    protected function __construct(?string $session_name = null, ?string $session_id = null)
    {
        $this->data = new CoverArray();

        $this->session_name = !empty($session_name) ? $session_name : session_name();

        session_name($this->session_name);

        // Устанавливаем $this->session_id именно таким образом, а не через $this->setId() потому,
        // что в $this->start() идёт проверка с использованием session_id().
        if (null !== $session_id) {
            $this->session_id = $session_id;
        }

        $this->start();
    }

    /**
     * Стартует сессию.
     */
    protected function start()
    {
        if (!$this->isStarted()) {
            if ($this->session_id) {
                $this->setId($this->session_id);
            }

            session_start();

            if (!$this->session_id) {
                $this->session_id = session_id();
            }

            if (!empty($_SESSION)) {
                foreach ($_SESSION as $key => $value) {
                    $this->data[$key] = is_array($value) ? new CoverArray($value) : $value;
                }
            }
        }
    }

    /**
     * Устанавливает ID сессии.
     *
     * @param string $session_id идентификатор сессии
     */
    protected function setId(string $session_id)
    {
        $session_id = trim($session_id);

        if (preg_match('~[^a-z0-9,\-]+~i', $session_id)) {
            throw new \InvalidArgumentException('Попытка присвоить некорректный ID сессии.');
        }

        session_id($session_id);
    }

    /**
     * Возвращает TRUE, если сессия уже стартовала, FALSE в противном случае.
     *
     * @return bool
     */
    protected function isStarted(): bool
    {
        return !(session_id() === '');
    }

    /**
     * Сохраняет данные объекта в сессии.
     */
    protected function save()
    {
        if ($this->data instanceof CoverArray && $this->data->count()) {
            $_SESSION = $this->data->getDataAsArray();
        }
    }
}