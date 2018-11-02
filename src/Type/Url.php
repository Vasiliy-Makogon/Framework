<?php

namespace Krugozor\Framework\Type;

/**
 * Тип `url адрес`.
 */
class Url implements TypeInterface
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
    public function __construct(?string $url)
    {
        $this->setValue($url);
    }

    /**
     * @return string
     */
    public function getValue(): ?string
    {
        return $this->url;
    }

    /**
     * @param string $value
     */
    public function setValue(?string $value)
    {
        $this->url = $value;
    }

    /**
     * Создает "красивый" якорь из длинного URL-адреса. Например, после обработки строки
     * <pre>http://test/admin/user/edit/?id=38&referer=http%3A%2F%2Ftest%2Fadmin%2Fuser%2F</pre>
     * будет получена строка вида <pre>http://test/admin/article/edit/?id=...%26sep%3D1</pre>
     *
     * @param int $width_prefix ширина префикса
     * @param int $width_postfix ширина постфикса
     * @param int $repeat кол-во повторений строки $simbol
     * @param string $simbol символ-заполнитель
     * @return string
     */
    public function getNiceAnchor($width_prefix = 20, $width_postfix = 10, $repeat = 3, $simbol = '.'): string
    {
        if (mb_strlen($this->url) > $width_prefix + $width_postfix) {
            return
                mb_substr($this->url, 0, $width_prefix) .
                str_repeat($simbol, $repeat) .
                mb_substr($this->url, -$width_postfix);
        }

        return $this->url;
    }
}