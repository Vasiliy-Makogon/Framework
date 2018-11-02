<?php

namespace Krugozor\Framework\Thumbnail;

class JpegCreator extends Creator
{
    /**
     * Сохраняет jpeg изображение в файловой системе
     *
     * @param resource $thumbnail ресурс изображения
     * @throws \RuntimeException
     */
    protected function storeImage($thumbnail)
    {
        if (!imageJPEG($thumbnail, $this->getFilePath(IMAGETYPE_JPEG), 100)) {
            throw new \RuntimeException('Невозможно сохранить jpeg файл изображения');
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