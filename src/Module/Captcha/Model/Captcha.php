<?php

namespace Krugozor\Framework\Module\Captcha\Model;

use Krugozor\Framework\Model\ImagePng;

/**
 * Пример использования:
 * $captcha = new Captcha('./path/to/font.ttf');
 * $_SESSION['code'] = $captcha->getCode();
 * $captcha->create();
 * $captcha->getImage();
 */
class Captcha extends ImagePng
{
    /**
     * Числовой код капчи.
     *
     * @var string
     */
    private $code;

    /**
     * Возвращает числовой код капчи (для дальнейшей передачи в сессию).
     *
     * @return string
     */
    public function getCode(): string
    {
        if ($this->code === null) {
            $this->code = (string) rand(1000, 9999);
        }

        return $this->code;
    }

    /**
     * Создает изображение капчи.
     */
    public function create()
    {
        $iw = 121;
        $ih = 51;

        $this->iresource = imagecreatetruecolor($iw, $ih);

        $w = imagecolorallocate($this->iresource, 255, 255, 255);

        imagefill($this->iresource, 0, 0, $w);

        // цвет линеек
        $g1 = imagecolorallocate($this->iresource, 192, 192, 192);

        // рисуем вертикальные линии
        for ($i = 0; $i <= $iw; $i += 5) imageline($this->iresource, $i, 0, $i, $ih, $g1);

        // рисуем горизонтальные линии
        for ($i = 0; $i <= $ih; $i += 5) imageline($this->iresource, 0, $i, $iw, $i, $g1);

        imagettftext($this->iresource, rand(25, 35), rand(-7, 7), 10 + rand(-5, 5), $ih - 10 + rand(-5, 5),
            $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code, 0, 1));

        imagettftext($this->iresource, rand(25, 35), rand(-7, 7), 30 + rand(-5, 5), $ih - 10 + rand(-5, 10),
            $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code, 1, 1));

        imagettftext($this->iresource, rand(25, 35), rand(-7, 7), 50 + rand(-5, 5), $ih - 10 + rand(-5, 5),
            $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code, 2, 1));

        imagettftext($this->iresource, rand(25, 35), rand(-7, 7), 70 + rand(-5, 5), $ih - 10 + rand(-10, 5),
            $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code, 3, 1));

        imagettftext($this->iresource, rand(25, 35), rand(-7, 7), 90 + rand(-5, 5), $ih - 10 + rand(-10, 5),
            $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code, 4, 1));
    }

    /**
     * Вывод изображения капчи в браузер.
     */
    public function showCaptcha()
    {
        imagepng($this->iresource);
    }

    /**
     * Возвращает случайный цвет для элемента капчи.
     *
     * @param $resource image resource
     * @return resource
     */
    private function getRandColor($resource)
    {
        return imagecolorallocate($resource, rand(0, 128), rand(0, 128), rand(0, 128));
    }
}