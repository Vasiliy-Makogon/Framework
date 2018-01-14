<?php

class Krugozor_Session implements Krugozor_Interface_Singleton
{
    private static $instance;

    /**
     * Хранилище данных, передаваемых через магические методы __set и __get.
     *
     * @var Krugozor_Cover_Array
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
     * @return Krugozor_Session
     */
    public static function getInstance($session_name = null, $session_id = null)
    {
        if (self::$instance === null) {
            self::$instance = new self($session_name, $session_id);
        }

        return self::$instance;
    }

    /**
     * Возвращает элемент из хранилища данных $this->data.
     *
     * @param void
     * @return mixed
     */
    public function __get($key)
    {
        return $this->data->$key;
    }

    /**
     * Добавляет новый элемент в хранилище данных $this->data.
     *
     * @param $key ключ
     * @param $value значение
     * @return void
     */
    public function __set($key, $value)
    {
        $this->data->$key = $value;
    }

    /**
     * Уничтожает сессию.
     *
     * @param void
     * @return void
     */
    public function destroy()
    {
        $_SESSION = array();
        $this->data = null;

        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }

        session_destroy();
    }

    /**
     * Возвращает имя сессии.
     *
     * @param void
     * @return string
     */
    public function getName()
    {
        return $this->session_name;
    }

    /**
     * Возвращает идентификатор сессии.
     *
     * @param void
     * @return string
     */
    public function getId()
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
     * @return void
     */
    protected function __construct($session_name = null, $session_id = null)
    {
        $this->data = new Krugozor_Cover_Array();

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
     *
     * @param void
     * @return void
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
                // Транслируем сессию во внутреннее представление класса-родителя Krugozor_Cover_Abstract_Array
                foreach ($_SESSION as $key => $value) {
                    $this->data[$key] = is_array($value) ? new Krugozor_Cover_Array($value) : $value;
                }
            }
        }
    }

    /**
     * Устанавливает ID сессии.
     *
     * @param string $sid идентификатор сессии
     * @return void
     */
    protected function setId($session_id)
    {
        $session_id = trim($session_id);

        if (preg_match('~[^a-z0-9,\-]+~i', $session_id)) {
            throw new InvalidArgumentException('Попытка присвоить некорректный ID сессии.');
        }

        session_id($session_id);
    }

    /**
     * Возвращает TRUE, если сессия уже стартовала, FALSE в противном случае.
     *
     * @param void
     * @return boolean
     */
    protected function isStarted()
    {
        return !(session_id() === '');
    }

    /**
     * Сохраняет данные объекта в сессии.
     *
     * @param void
     * @return void
     */
    protected function save()
    {
        if ($this->data instanceof Krugozor_Cover_Array && $this->data->count()) {
            $_SESSION = $this->data->getDataAsArray();
        }
    }
}