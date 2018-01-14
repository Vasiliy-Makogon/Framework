<?php

/**
 * Тип модели `url адрес`.
 */
class Krugozor_Type_Url implements Krugozor_Type_Interface
{
    /**
     * URL адрес.
     *
     * @var string
     */
    protected $url;

    /**
     * @param string $url
     */
    public function __construct($url)
    {
        $this->setValue($url);
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Type_Interface::getValue()
     */
    public function getValue()
    {
        return $this->url;
    }

    /**
     * @param string $value
     * @return void
     */
    public function setValue($value)
    {
        $this->url = $value;
    }

    /**
     * Создает "красивый" якорь из длинного URL-адреса. Например, после обработки строки
     * <pre>http://test/admin/user/edit/?id=38&referer=http%3A%2F%2Ftest%2Fadmin%2Fuser%2F</pre>
     * будет получена строка вида <pre>http://test/admin/article/edit/?id=...%26sep%3D1</pre>
     *
     * @param string $url гиперссылка
     * @param string $simbol символ- или строка- заполнитель
     * @param string $repeat количество повторений $simbol
     * @param int $ml_url_width_prefix количество символов, оставляемых спереди
     * @param int $ml_url_width_postfix количество символов, оставляемых позади
     *
     * @param void
     * @return string
     */
    public function getNiceAnchor($width_prefix = 20, $width_postfix = 10, $repeat = 3, $simbol = '.')
    {
        if (mb_strlen($this->url) > $width_prefix + $width_postfix) {
            return mb_substr($this->url, 0, $width_prefix) . str_repeat($simbol, $repeat) . mb_substr($this->url, -$width_postfix);
        }

        return $this->url;
    }
}