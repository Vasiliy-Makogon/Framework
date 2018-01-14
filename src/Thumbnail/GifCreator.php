<?php

class Krugozor_Thumbnail_GifCreator extends Krugozor_Thumbnail_Creator
{
    /**
     * Сохраняет gif изображение в файловой системе
     *
     * @param resource ресурс изображения
     * @throws RuntimeException
     */
    protected function storeImage($thumbnail)
    {
        if (!imageGIF($thumbnail, $this->getFilePath(IMAGETYPE_GIF))) {
            throw new RuntimeException('Невозможно сохранить gif файл изображения');
        }

        return true;
    }

    /**
     * Возвращает ссылку на ресурс изображения
     *
     * @return resource
     */
    protected function getSourceImage()
    {
        return imageCreateFromGIF($this->source_image);
    }
}