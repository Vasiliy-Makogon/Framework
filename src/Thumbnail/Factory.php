<?php

namespace Krugozor\Framework\Thumbnail;

class Factory
{
    /**
     * @param string $uploadedFile путь к исходному файлу
     * @param string $destinationFile путь к файлу назначения
     * @throws UnexpectedValueException
     * @return GifCreator|JpegCreator|PngCreator
     */
    public static function create($uploadedFile, $destinationFile)
    {
        list(, , $type,) = getimagesize($uploadedFile);

        switch ($type) {
            case IMAGETYPE_GIF:
                return new GifCreator($uploadedFile, $destinationFile);

            case IMAGETYPE_JPEG:
                return new JpegCreator($uploadedFile, $destinationFile);

            case IMAGETYPE_PNG:
                return new PngCreator($uploadedFile, $destinationFile);

            default:
                throw new \UnexpectedValueException('Передан неизвестный тип файла изображения');
        }
    }
}