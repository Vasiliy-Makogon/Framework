<?php

/**
 * Класс flash-уведомлений на основе редиректа.
 * Используется так:
 *
 * $notification = new Krugozor_Notification($databaseInstance);
 * $notification->setMessage('Пользователь {user_name} (ID: {user_id}) сохранён');
 * $notification->addParam('user_name', $this->user->getFullName());
 * $notification->addParam('id_user', $this->user->getId());
 * $notification->setNotificationUrl($url);
 * $notification->run();
 */
class Krugozor_Notification
{
    /**
     * Типы сообщений для пользователя.
     */
    const TYPE_ALERT = 'alert';

    const TYPE_NORMAL = 'normal';

    const TYPE_WARNING = 'warning';

    /**
     * @var \Krugozor\Database\Mysql\Mysql
     */
    private $db;

    /**
     * ID сообщения.
     *
     * @var int
     */
    private $id_notification;

    /**
     * Скрытое сообщение (true) или нет (false).
     *
     * @var bool
     */
    private $is_hidden = false;

    /**
     * Тип сообщения. Может быть трёх любых типов:
     * self::TYPE_NORMAL  - сообщение об успешном выполнении.
     * self::TYPE_ALERT   - сообщение при ошибке пользователя или системы,
     *                      отменившее выполнение какого-либо действия.
     * self::TYPE_WARNING - сообщение об успешном выполнении, но предупреждающее о чем-либо.
     *
     * @var string
     */
    private $notification_type = self::TYPE_NORMAL;

    /**
     * Заголовок сообщения.
     *
     * @var string
     */
    private $notification_header;

    /**
     * Тело сообщения.
     *
     * @var string
     */
    private $notification_message;

    /**
     * Параметры для подстановки в тело сообщения в виде ассоциативного массива.
     *
     * @var array
     */
    private $notification_params = array();

    /**
     * URL для перенаправления (если нужно использовать перенаправление).
     *
     * @var string
     */
    private $redirect_url;

    /**
     * Удалять ли уведомление после показа (true) или нет (false).
     *
     * @var bool
     */
    private $notification_remove = true;

    /**
     * @param \Krugozor\Database\Mysql\Mysql $db
     */
    public function __construct(\Krugozor\Database\Mysql\Mysql $db)
    {
        $this->db = $db;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id_notification;
    }

    /**
     * @return bool
     */
    public function getHidden()
    {
        return $this->is_hidden;
    }

    /**
     * @param bool $is_hidden
     * @return Krugozor_Notification
     */
    public function setHidden($is_hidden = true)
    {
        $this->is_hidden = (bool)$is_hidden;

        return $this;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->notification_type;
    }

    /**
     * @param string $type
     * @return Krugozor_Notification
     */
    public function setType($type)
    {
        if (null !== $type) {
            $this_class = new ReflectionClass(__CLASS__);
            $constants = array_values($this_class->getConstants());

            if (!in_array($type, $constants)) {
                trigger_error(__METHOD__ . ': Указан некорректный тип ' . $type);

                $this->notification_type = self::TYPE_NORMAL;
            } else {
                $this->notification_type = $type;
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getHeader()
    {
        return $this->notification_header;
    }

    /**
     * @param string $header
     * @return Krugozor_Notification
     */
    public function setHeader($header)
    {
        $this->notification_header = $header;

        return $this;
    }

    /**
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->redirect_url;
    }

    /**
     * @param string $url
     * @return Krugozor_Notification
     */
    public function setNotificationUrl($url)
    {
        $this->redirect_url = (string)$url;

        return $this;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return Krugozor_Static_String::createMessageFromParams($this->notification_message, $this->notification_params);
    }

    /**
     * @param string $message
     * @return Krugozor_Notification
     */
    public function setMessage($message)
    {
        $this->notification_message = (string)$message;

        return $this;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return Krugozor_Notification
     */
    public function addParam($key, $value)
    {
        $this->notification_params[$key] = $value;

        return $this;
    }

    /**
     * @param array $params
     */
    public function addParams(array $params = array())
    {
        foreach ($params as $key => $value) {
            $this->addParam($key, $value);
        }
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->notification_params;
    }

    /**
     * Устанавливает флаг удаления уведомления.
     *
     * @param bool $value
     * @return Krugozor_Notification
     */
    public function setRemoveNotificationFlag($value = true)
    {
        $this->notification_remove = (bool)$value;

        return $this;
    }

    /**
     * Записывает в базу сообщение и отсылает заголовок Location на $this->redirect_url.
     * Запись в базу будет осуществляться только в том случае, если свойство
     * $this->is_hidden определено как FALSE (не "скрытый" редирект, т.е. с выводом сообщения).
     *
     * @param void
     * @return Krugozor_Notification
     */
    public function run()
    {
        if (!$this->is_hidden) {
            $sql = 'INSERT INTO `notifications`
                    SET
                    `notification_remove` = ?i,
                    `notification_hidden` = ?i,
                    `notification_type` = "?s",
                    `notification_header` = "?s",
                    `notification_message` = "?s",
                    `notification_params` = "?s",
                    `notification_date` = NOW()';

            $this->db->query(
                $sql,
                $this->notification_remove,
                $this->is_hidden,
                $this->notification_type,
                $this->notification_header,
                $this->notification_message,
                serialize($this->notification_params)
            );

            $this->redirect_url .= strpos($this->redirect_url, '?') !== false ? '&' : '?';
            $this->redirect_url .= 'notif=' . $this->db->getLastInsertId();
        }

        return $this;
    }

    /**
     * Получает информацию о совершившемся действии на основании
     * идентификатора $id записи в таблице сообщений.
     *
     * @param int
     * @return void
     */
    public function findById($id)
    {
        $res = $this->db->query('
          SELECT
             `notification_remove`,
             `notification_hidden`,
             `notification_type` as type,
             `notification_header` as header,
             `notification_message` as message,
             `notification_params` as params
          FROM `notifications`
          WHERE `id_notification` = ?i
          LIMIT 0, 1', $id);

        if ($data = $res->fetch_assoc()) {
            $this->id_notification = $id;
            $this->notification_remove = (bool)$data['notification_remove'];
            $this->is_hidden = (bool)$data['notification_hidden'];
            $this->notification_type = $data['type'];
            $this->notification_header = $data['header'];
            $this->notification_message = $data['message'];
            $this->notification_params = unserialize($data['params']);

            if ($this->notification_remove) {
                $this->db->query('DELETE FROM `notifications` WHERE `id_notification` = ?i', $this->id_notification);
            }
        }
    }

    /**
     * Очистка таблицы уведомлений.
     * Метод для cron.
     *
     * @param void
     * @return int количество задействованных рядов
     */
    public function truncateTable()
    {
        $this->db->query('TRUNCATE TABLE `notifications`');
    }
}