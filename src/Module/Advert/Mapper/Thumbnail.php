<?php

namespace Krugozor\Framework\Module\Advert\Mapper;

use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Module\Advert\Model\Thumbnail as ThumbnailModel;
use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Cover\CoverArray;

class Thumbnail extends CommonMapper
{
    /**
     * Связывает запись об изображении $thumbnail с объявлением $advert.
     *
     * @param ThumbnailModel $thumbnail
     * @param Advert $advert
     * @return bool|\Krugozor\Database\Mysql\Statement
     */
    public function updateByAdvert(ThumbnailModel $thumbnail, Advert $advert)
    {
        $sql = 'UPDATE ?f SET ?f = ?i WHERE id = ?i AND ?f IS NULL LIMIT 1';

        return $this->getDb()->query($sql,
            $this->getTableName(),
            ThumbnailModel::getPropertyFieldName('id_advert'),
            $advert->getId(),
            $thumbnail->getId(),
            ThumbnailModel::getPropertyFieldName('id_advert')
        );
    }

    /**
     * Возвращает все изображения объявления $advert.
     *
     * @param Advert $advert
     * @return CoverArray
     */
    public function findByAdvert(Advert $advert)
    {
        $params = array(
            'where' => array('id_advert = ?i' => array($advert->getId())),
            'order' => array('file_date' => 'ASC')
        );

        return parent::findModelListByParams($params);
    }

    /**
     * Возвращает список объектов изображений, не привязанных к объявлениям (NULL в поле `id_advert`).
     * Далее у каждого объекта модели в клиентском коде исполняется метод delete().
     * Метод для cron.
     *
     * @param int $period_minutes период в минутах от текщей даты
     * @param int $count кол-во удаляемых изображений
     * @return CoverArray|boolean
     */
    public function getThumbnailsNotRelatedToAdverts($period_minutes=60, $count=200)
    {
        $sql = 'SELECT * FROM ?f
                WHERE ?f IS NULL
                AND ?f < (NOW() - INTERVAL ?i MINUTE)
                ORDER BY ?f ASC LIMIT ?i';

        return parent::findModelListBySql($sql,
            $this->getTableName(),
            ThumbnailModel::getPropertyFieldName('id_advert'),
            ThumbnailModel::getPropertyFieldName('file_date'),
            $period_minutes,
            ThumbnailModel::getPropertyFieldName('id'),
            $count
        );
    }

    /**
     * Разрывает связь между изображением и объявлением, к которому прикреплено изображение.
     * Далее изображение удаляет cron. См. метод self::getThumbnailsNotRelatedToAdverts()
     *
     * @param ThumbnailModel $thumbnail
     * @return int кол-во затронутых рядов
     */
    public function unlink(ThumbnailModel $thumbnail)
    {
        $sql = 'UPDATE ?f
                SET ?f = ?n
                WHERE `id` = ?i
                LIMIT 1';

        $this->getDb()->query($sql,
            $this->getTableName(),
            ThumbnailModel::getPropertyFieldName('id_advert'),
            null,
            $thumbnail->getId()
        );

        return $this->getDb()->getAffectedRows();
    }
}