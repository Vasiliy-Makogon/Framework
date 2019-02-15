<?php

namespace Krugozor\Framework;

/**
 * Класс-обёртка для работы с mail()
 * Используется следующим образом:
 *
 * $mail = new Mail();
 * $mail->setTo('info@host.com');
 * $mail->setFrom('robot@server.com');
 * $mail->setReplyTo('robot@server.com');
 * $mail->setHeader('Заголовок письма');
 *
 * // шаблон письма в стиле PHP-pure
 * $mail->setTemplate('/path/to/template.mail');
 *
 * // данные для шаблона любого PHP-типа
 * $mail->user = $this->user;
 * $mail->new_password = $new_password;
 *
 * $this->mail->send();
 */
class Mail
{
    /**
     * Данные шаблона.
     *
     * @var array
     */
    private $_data = array();

    /**
     * Тип письма.
     * text или html, по умолчанию text
     *
     * @var string
     */
    private $type = 'text';

    /**
     * mime-типы
     *
     * @var array
     * @static
     */
    private static $types = array(
        'text' => 'text/plain',
        'html' => 'text/html',
    );

    /**
     * Заголовок письма.
     *
     * @var string
     */
    private $_header;

    /**
     * Email-адрес адресата.
     *
     * @var string
     */
    private $_to;

    /**
     * Путь до файла почтового шаблона.
     *
     * @var string
     */
    private $_tpl_file;

    /**
     * Язык письма.
     *
     * @var string
     */
    private $_lang = 'ru';

    /**
     * Дополнительные HTTP-заголовки письма.
     *
     * @var array
     */
    private $_additional_headers = [];

    /**
     * Устанавливает данные $value под индексом $key во
     * внутреннее представление для подстановки в шаблон письма.
     * Данные $value могут быть любого типа.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set($key, $value)
    {
        $this->_data[$key] = $value;
    }

    /**
     * Возвращает данные из внутреннего представления
     * под индексом $key.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get($key)
    {
        return isset($this->_data[$key]) ? $this->_data[$key] : null;
    }

    /**
     * Устанавливает тип письма: text или html
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        if (!isset(self::$types[$type])) {
            throw new \InvalidArgumentException(__METHOD__ . ': Unknown letter type specified');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Устанавливает Email-адрес адресата.
     * @param string $_to
     * @return $this
     */
    public function setTo($_to)
    {
        $this->_to = $_to;

        return $this;
    }

    /**
     * Устанавливает язык письма.
     * @param string $_lang
     * @return $this
     */
    public function setLang($_lang)
    {
        $this->_lang = $_lang;

        return $this;
    }

    /**
     * Устанавливает Email-адрес отправителя.
     * @param string $value
     * @return $this
     */
    public function setFrom($value)
    {
        if ($value) {
            $this->_additional_headers['From'] = $value;
        }

        return $this;
    }

    /**
     * Устанавливает Email-адрес для ответа.
     * @param string $value
     * @return $this
     */
    public function setReplyTo($value)
    {
        if ($value) {
            $this->_additional_headers['Reply-To'] = $value;
        }

        return $this;
    }

    /**
     * Устанавливает копию Email-адреса адресата.
     *
     * @param string $value
     * @return $this
     */
    public function setCc($value)
    {
        if ($value) {
            $this->_additional_headers['Cc'] = $value;
        }

        return $this;
    }

    /**
     * Принимает путь к файлу шаблона.
     *
     * @param string $tpl_file
     * @return $this
     */
    public function setTemplate($tpl_file)
    {
        if (!file_exists($tpl_file)) {
            new \RuntimeException(__METHOD__ . ': No mail template found at ' . $tpl_file);
        }

        $this->_tpl_file = $tpl_file;

        return $this;
    }

    /**
     * Устанавливает заголовок письма.
     *
     * @param string $_header
     * @return $this
     */
    public function setHeader($_header)
    {
        $this->_header = $_header;

        return $this;
    }

    /**
     * Отправляет письмо.
     *
     * @return bool
     */
    public function send()
    {
        $additional_headers = [
            'Content-type' => self::$types[$this->type] . '; charset=utf-8',
            'Content-language' => $this->_lang,
            'X-Mailer' => 'PHP/' . phpversion(),
            'Date' => date("r"),
        ];

        $headers = array_merge($additional_headers, $this->_additional_headers);

        if (empty($headers['Reply-To']) && !empty($headers['From'])) {
            $headers['Reply-To'] = $headers['From'];
        }

        $additional_headers_buffer = [];
        foreach ($headers as $k => $v) {
            $additional_headers_buffer[] = "$k: $v";
        }

        ob_start();
        include($this->_tpl_file);
        $message = ob_get_contents();
        ob_end_clean();

        return mb_send_mail($this->_to, $this->_header, $message, implode(PHP_EOL, $additional_headers_buffer));
    }
}