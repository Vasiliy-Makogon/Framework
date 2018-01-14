<?php

class Krugozor_Thumbnail_JpegCreator extends Krugozor_Thumbnail_Creator
{
    /**
     * Сохраняет jpeg изображение в файловой системе
     *
     * @param resource ресурс изображения
     * @throws RuntimeException
     */
    protected function storeImage($thumbnail)
    {
        if (!imageJPEG($thumbnail, $this->getFilePath(IMAGETYPE_JPEG), 100)) {
            throw new RuntimeException('Невозможно сохранить jpeg файл изображения');
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
        return imageCreateFromJPEG($this->source_image);
    }
}