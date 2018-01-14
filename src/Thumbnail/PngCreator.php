<?php

class Krugozor_Thumbnail_PngCreator extends Krugozor_Thumbnail_Creator
{
    /**
     * Сохраняет png изображение в файловой системе
     *
     * @param resource ресурс изображения
     * @throws RuntimeException
     */
    protected function storeImage($thumbnail)
    {
        if (!imagePNG($thumbnail, $this->getFilePath(IMAGETYPE_PNG))) {
            throw new RuntimeException('Невозможно сохранить png файл изображения');
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
        return imageCreateFromPNG($this->source_image);
    }
}