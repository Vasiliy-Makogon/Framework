<?php
class Krugozor_Module_Advert_Mapper_Thumbnail extends Krugozor_Mapper_Common
{
    /**
     * Связывает запись об изображении $thumbnail с объявлением $advert.
     *
     * @param int $id ID записи, хранящий информацию об изображении
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @return bool
     */
    public function updateByAdvert(Krugozor_Module_Advert_Model_Thumbnail $thumbnail, Krugozor_Module_Advert_Model_Advert $advert)
    {
        $sql = 'UPDATE ?f SET ?f = ?i WHERE id = ?i AND ?f IS NULL LIMIT 1';

        return $this->getDb()->query($sql,
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Thumbnail::getPropertyFieldName('id_advert'),
            $advert->getId(),
            $thumbnail->getId(),
            Krugozor_Module_Advert_Model_Thumbnail::getPropertyFieldName('id_advert')
        );
    }

    /**
     * Возвращает все изображения объявления $advert.
     *
     * @param Krugozor_Module_Advert_Model_Advert $advert
     * @return Krugozor_Cover_Array
     */
    public function findByAdvert(Krugozor_Module_Advert_Model_Advert $advert)
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
     * @return Krugozor_Cover_Array|boolean
     */
    public function getThumbnailsNotRelatedToAdverts($period_minutes=60, $count=200)
    {
        $sql = 'SELECT * FROM ?f
                WHERE ?f IS NULL
                AND ?f < (NOW() - INTERVAL ?i MINUTE)
                ORDER BY ?f ASC LIMIT ?i';

        return parent::findModelListBySql($sql,
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Thumbnail::getPropertyFieldName('id_advert'),
            Krugozor_Module_Advert_Model_Thumbnail::getPropertyFieldName('file_date'),
            $period_minutes,
            Krugozor_Module_Advert_Model_Thumbnail::getPropertyFieldName('id'),
            $count
        );
    }

    /**
     * Разрывает связь между изображением и объявлением, к которому прикреплено изображение.
     * Далее изображение удаляет cron. См. метод self::getThumbnailsNotRelatedToAdverts()
     *
     * @param Krugozor_Module_Advert_Model_Thumbnail $thumbnail
     * @return int кол-во затронутых рядов
     */
    public function unlink(Krugozor_Module_Advert_Model_Thumbnail $thumbnail)
    {
        $sql = 'UPDATE ?f
                SET ?f = ?n
                WHERE `id` = ?i
                LIMIT 1';

        $this->getDb()->query($sql,
            $this->getTableName(),
            Krugozor_Module_Advert_Model_Thumbnail::getPropertyFieldName('id_advert'),
            null,
            $thumbnail->getId()
        );

        return $this->getDb()->getAffectedRows();
    }
}