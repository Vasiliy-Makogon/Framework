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
 * $mail->setTemplate('../../template.mail');
 *
 * // данные для шаблона любого типа
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
    private $data = array();

    /**
     * Тип письма.
     * text или html, по умолчанию text
     *
     * @var string
     */
    private $type;

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
    private $header;

    /**
     * Сформированное тело письма, после отправки.
     *
     * @var string
     */
    private $message;

    /**
     * Email-адрес адресата.
     *
     * @var string
     */
    private $to;

    /**
     * Email-адрес отправителя.
     *
     * @var string
     */
    private $from;

    /**
     * Email-адрес для ответа.
     *
     * @var string
     */
    private $reply_to;

    /**
     * Путь до файла почтового шаблона.
     *
     * @var string
     */
    private $tpl_file;

    /**
     * Язык письма.
     *
     * @var string
     */
    private $lang;

    /**
     * HTTP-заголовки
     *
     * @var array
     */
    private $headers;

    /**
     * Mail constructor.
     */
    public function __construct()
    {
        $this->type = 'text';
        $this->lang = 'ru';
    }

    /**
     * Устанавливает данные $value под индексом $key во
     * внутреннее представление для подстановки в шаблон письма.
     * Данные $value могут быть любого типа.
     *
     * @param string $key
     * @param mixed $value
     */
    public function __set(string $key, $value)
    {
        $this->data[$key] = $value;
    }

    /**
     * Возвращает данные из внутреннего представления
     * под индексом $key.
     *
     * @param string $key
     * @return mixed|null
     */
    public function __get(string $key)
    {
        return isset($this->data[$key]) ? $this->data[$key] : null;
    }

    /**
     * Устанавливает тип письма: text или html
     *
     * @param string $type
     * @return Mail
     */
    public function setType(string $type): self
    {
        if (!isset(self::$types[$type])) {
            throw new \InvalidArgumentException(__METHOD__ . ': Указан неизвестный тип письма');
        }

        $this->type = $type;

        return $this;
    }

    /**
     * Устанавливает Email-адрес адресата.
     *
     * @param string $to
     * @return Mail
     */
    public function setTo(string $to): self
    {
        $this->to = $to;

        return $this;
    }

    /**
     * Устанавливает язык письма.
     *
     * @param string $lang
     * @return Mail
     */
    public function setLang(string $lang): self
    {
        $this->lang = $lang;

        return $this;
    }

    /**
     * Устанавливает Email-адрес отправителя.
     *
     * @param string $from
     * @return Mail
     */
    public function setFrom(string $from): self
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Устанавливает Email-адрес для ответа.
     *
     * @param string $reply_to
     * @return Mail
     */
    public function setReplyTo(string $reply_to): self
    {
        $this->reply_to = $reply_to;

        return $this;
    }

    /**
     * Принимает путь к файлу шаблона. Файл должен существовать.
     *
     * @param string $tpl_file
     * @return Mail
     */
    public function setTemplate(string $tpl_file): self
    {
        if (!file_exists($tpl_file)) {
            new \RuntimeException(__METHOD__ . ': Не найден почтовый шаблон ' . $tpl_file);
        }

        $this->tpl_file = $tpl_file;

        return $this;
    }

    /**
     * Устанавливает заголовок письма.
     *
     * @param string $header
     * @return Mail
     */
    public function setHeader(string $header): self
    {
        $this->header = $header;

        return $this;
    }

    /**
     * Отправляет письмо.
     *
     * @return bool
     */
    public function send(): bool
    {
        $this->headers =
            'Content-type: ' . self::$types[$this->type] . '; charset=utf-8' . PHP_EOL .
            'Content-language: ' . $this->lang . PHP_EOL .
            'From: ' . $this->from . PHP_EOL .
            'X-Mailer: PHP/' . phpversion() . PHP_EOL .
            'Date: ' . date("r") . PHP_EOL .
            'Reply-To: ' . ($this->reply_to ? $this->reply_to : $this->from);

        ob_start();
        include($this->tpl_file);
        $this->message = ob_get_contents();
        ob_end_clean();

        return mb_send_mail($this->to, $this->header, $this->message, $this->headers);
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }
}