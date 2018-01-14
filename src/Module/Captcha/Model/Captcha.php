<?php
/**
 * Пример использования:
 * $captcha = new Krugozor_Module_Captcha_Model_Captcha('./path/to/font.ttf');
 * $_SESSION['code'] = $captcha->getCode();
 * $captcha->create();
 * $captcha->getImage();
 */
class Krugozor_Module_Captcha_Model_Captcha extends Krugozor_Model_ImagePng
{
    /**
     * Числовой код капчи.
     *
     * @access private
     * @var string
     */
    private $code;

    /**
     * Возвращает числовой код капчи.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getCode()
    {
        if ($this->code === null) {
            $this->code = substr(preg_replace('/[a-z]/', '', md5(time().rand(1,100))), 0, 4);
        }

        return $this->code;
    }

    /**
     * Создает изображение капчи.
     *
     * @access public
     * @param void
     * @return void
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
        for ($i=0; $i<=$iw; $i+=5) imageline($this->iresource,$i,0,$i,$ih,$g1);

        // рисуем горизонтальные линии
        for ($i=0; $i<=$ih; $i+=5) imageline($this->iresource,0,$i,$iw,$i,$g1);

        imagettftext($this->iresource, rand(25,35), rand(-7,7), 10+rand(-5,5), $ih-10+rand(-5,5),
        $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code,0,1));

        imagettftext($this->iresource, rand(25,35), rand(-7,7), 30+rand(-5,5), $ih-10+rand(-5,10),
        $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code,1,1));

        imagettftext($this->iresource, rand(25,35), rand(-7,7), 50+rand(-5,5), $ih-10+rand(-5,5),
        $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code,2,1));

        imagettftext($this->iresource, rand(25,35), rand(-7,7), 70+rand(-5,5), $ih-10+rand(-10,5),
        $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code,3,1));

        imagettftext($this->iresource, rand(25,35), rand(-7,7), 90+rand(-5,5), $ih-10+rand(-10,5),
        $this->getRandColor($this->iresource), $this->path_ttf, substr($this->code,4,1));
    }

    /**
     * Вывод изображения капчи в браузер.
     *
     * @param void
     * @return void
     */
    public function showCaptcha()
    {
        imagepng($this->iresource);
    }

    /**
     * Возвращает случайный цвет для элемента капчи.
     *
     * @access private
     * @param $in image resource
     * @return image resource
     */
    private function getRandColor($in)
    {
        return imagecolorallocate($in, rand(0,128), rand(0,128), rand(0,128));
    }
}