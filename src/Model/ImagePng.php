<?php

namespace Krugozor\Framework\Model;

abstract class ImagePng
{
    /**
     * Путь до файла шрифта ttf
     *
     * @var string
     */
    protected $path_ttf;

    /**
     * Ресурс GD
     *
     * @var resource
     */
    protected $iresource;

    /**
     * Основной метод создающий изображение
     * пригодное для вывода через imagepng()
     *
     * @abstract
     */
    abstract public function create();

    /**
     * ImagePng constructor.
     * @param string $ttf
     */
    public function __construct(string $ttf)
    {
        if (!file_exists($ttf)) {
            throw new \InvalidArgumentException('Не найден файл шрифта по адресу ' . $ttf);
        }

        $this->path_ttf = $ttf;
    }

    /**
     * Возвращает цветовой идентификатор (см. imagecolorallocate ())
     * на основании шестнадцатеричной записи цвета.
     *
     * @param string строка цвета в hex
     * @return int|bool
     * @see imagecolorallocate()
     */
    protected function getRgbByHex(string $color)
    {
        if (preg_match('#[a-f0-9]{6}#i', $color)) {
            return imagecolorallocate($this->iresource,
                hexdec('0x' . $color[0] . $color[1]),
                hexdec('0x' . $color[2] . $color[3]),
                hexdec('0x' . $color[4] . $color[5]));
        }

        return false;
    }
}