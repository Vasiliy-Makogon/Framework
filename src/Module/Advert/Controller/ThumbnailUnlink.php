<?php

namespace Krugozor\Framework\Module\Advert\Controller;

use Krugozor\Framework\Controller\Ajax;
use Krugozor\Framework\Module\Advert\Model\Advert;

/**
 * Отвязывает одно изображение от объявления.
 * Обработчик Ajax-запросов из управления изображениями.
 * Далее эти изображения удаляются с помощью cron-скрипта remove_thumbnail_advert.php
 */
class ThumbnailUnlink extends Ajax
{
    public function run()
    {
        $num_rows = 0;

        $thumbnail = $this->getMapper('Advert/Thumbnail')->findModelById(
            $this->getRequest()->getRequest('id', 'decimal')
        );

        if ($thumbnail->getId()) {
            /* @var $advert Advert */
            $advert = $this->getMapper('Advert/Advert')->findModelById($thumbnail->getIdAdvert());

            if ($advert->belongToRegisterUser($this->getCurrentUser()) || $this->getCurrentUser()->isAdministrator()) {
                $num_rows = $this->getMapper('Advert/Thumbnail')->unlink($thumbnail);
            }
        }

        $this->getView()->result = $num_rows;

        return $this->getView();
    }
}