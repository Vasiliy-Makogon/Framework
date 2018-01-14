<?php
/**
 * Принцип удаления изображений:
 *
 * Вызывается метод Krugozor_Module_Advert_Mapper_Thumbnail::unlink($thumbnail) - изображение отвязывается от сущности в конкретном контроллере.
 * Далее в CRON-обработчике вызывается Krugozor_Module_Advert_Mapper_Thumbnail::getThumbnailsNotRelatedToAdverts(),
 * для каждой модели изображения вызывается метод delete().
 */
class Krugozor_Module_Advert_Model_Thumbnail extends Krugozor_Model
{
    protected static $db_field_prefix = '';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'db_field_name' => 'id',
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array(),
            )
        ),

        'id_advert' => array(
            'db_element' => true,
            'db_field_name' => 'id_advert',
            'default_value' => null,
            'validators' => array(
                'Decimal' => array(),
            )
        ),

        'file_name' => array(
            'db_element' => true,
            'db_field_name' => 'file_name',
            'default_value' => null,
            'validators' => array(
                'IsNotEmpty' => array(),
                'StringLength' => array('start'=> 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
            )
        ),

        'file_date' => array(
            'type' => 'Krugozor_Type_Datetime',
            'db_element' => true,
            'db_field_name' => 'file_date',
            'default_value' => 'now'
        ),
    );

    /**
     * HTTP-путь к изображению.
     *
     * @var string
     */
    protected $full_http_path;

    /**
     * На основе имени файла $this->file_name (например, d2d8f9c20083bd8483ac5d5526f923b9.jpeg)
     * возвращает полный путь к файлу для HTTP, вида /d/2/d/8/f/d2d8f9c20083bd8483ac5d5526f923b9.jpeg
     *
     * @param void
     * @return string HTTP-путь к файлу
     */
    public function getFullHttpPath()
    {
        if (!$this->file_name) {
            return false;
        }

        if (!$this->full_http_path) {
            $directory_generator = new Krugozor_Utility_Upload_DirectoryGenerator($this->file_name);
            $this->full_http_path = $directory_generator->getHttpPath();
        }

        return $this->full_http_path . $this->file_name;
    }

    /**
     * Удаляет файлы изображений с файловой системы и информацию о них из СУБД.
     * Метод для cron, не вызывается в клиентском коде.
     *
     * @param void
     * @throws RuntimeException
     * @return int количество удаленных рядов или false
     */
    public function delete()
    {
        $directory_generator = new Krugozor_Utility_Upload_DirectoryGenerator($this->file_name);
        $file_1 = $directory_generator->create(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_150x100']) . $this->file_name;
        $file_2 = $directory_generator->create(DOCUMENTROOT_PATH . Krugozor_Registry::getInstance()->UPLOAD['THUMBNAIL_800x800']) . $this->file_name;

        if (!@unlink($file_1) && file_exists($file_1)) {
            throw new RuntimeException('Failed to delete the image file ' . $file_1);
        }

        if (!@unlink($file_2) && file_exists($file_2)) {
            throw new RuntimeException('Failed to delete the image file ' . $file_2);
        }

        if ($num_rows = $this->getMapperManager()->getMapper('Advert/Thumbnail')->deleteById($this)) {
            return $num_rows;
        }

        return false;
    }
}