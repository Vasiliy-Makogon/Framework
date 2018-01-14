<?php

class Krugozor_Thumbnail_Factory
{
    /**
     * @param string $uploadedFile путь к исходному файлу
     * @param string $destinationFile путь к файлу назначения
     * @throws UnexpectedValueException
     * @return Krugozor_Thumbnail_GifCreator|Krugozor_Thumbnail_JpegCreator|Krugozor_Thumbnail_PngCreator
     */
    public static function create($uploadedFile, $destinationFile)
    {
        list(, , $type,) = getimagesize($uploadedFile);

        switch ($type) {
            case IMAGETYPE_GIF:
                return new Krugozor_Thumbnail_GifCreator($uploadedFile, $destinationFile);

            case IMAGETYPE_JPEG:
                return new Krugozor_Thumbnail_JpegCreator($uploadedFile, $destinationFile);

            case IMAGETYPE_PNG:
                return new Krugozor_Thumbnail_PngCreator($uploadedFile, $destinationFile);

            default:
                throw new UnexpectedValueException('Передан неизвестный тип файла изображения');
        }
    }
}