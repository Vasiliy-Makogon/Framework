<?php

class Krugozor_Module_Advert_Model_Advert extends Krugozor_Model
{
    protected static $db_field_prefix = 'advert';

    /**
     * Кол-во дней, после которых неоплаченные объявления (свойство payment=0) из платных категорий,
     * отмеченных как paid_tolerance=1, устанавливаются в статус "оплачено" (с помощью cron-скрипта).
     * Это сделано для того, что бы не терять контент, который жадные люди не хотят оплачивать.
     *
     * @var int
     */
    const PAID_TOLERANCE_DAYS = 1;

    /**
     * Минимальное кол-во объявлений с VIP-статусом, которые должны присутствовать в системе.
     *
     * @var int
     */
    const MIN_ADVERTS_WITH_VIP_STATUSES = 8;

    /**
     * Минимальное кол-во объявлений с Special-статусом, которые должны присутствовать в системе.
     *
     * @var int
     */
    const MIN_ADVERTS_WITH_SPECIAL_STATUSES = 10;

    /**
     * Паттерн для создания md5-хэшей объявлений.
     *
     * @var string
     */
    protected static $text_hash_pattern = '#[а-яa-z]{4,}#i';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'id_user' => array(
            'db_element' => true,
            'db_field_name' => 'advert_id_user',
            'default_value' => -1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => true),
            )
        ),

        'unique_user_cookie_id' => array(
            'db_element' => true,
            'db_field_name' => 'advert_unique_user_cookie_id',
            'record_once' => true,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::MD5_MAX_LENGTH),
                'CharPassword' => array(),
            )
        ),

        'active' => array(
            'db_element' => true,
            'db_field_name' => 'advert_active',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'type' => array(
            'type' => 'Krugozor_Module_Advert_Type_AdvertType',
            'db_element' => true,
            'db_field_name' => 'advert_type',
            'default_value' => null,
            'validators' => array(
                'IsNotEmpty' => array(),
                'VarEnum' => array('enum' => array('sale', 'buy')),
            )
        ),

        'user_type' => array(
            'db_element' => true,
            'type' => 'Krugozor_Module_User_Type_Type',
            'db_field_name' => 'advert_user_type',
            'default_value' => null,
            'validators' => array(
                'IsNotEmpty' => array(),
                'VarEnum' => array('enum' => array('private_person', 'company')),
            )
        ),

        'category' => array(
            'db_element' => true,
            'db_field_name' => 'advert_category',
            'default_value' => null,
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'header' => array(
            'db_element' => true,
            'db_field_name' => 'advert_header',
            'default_value' => null,
            'validators' => array(
                'IsNotEmpty' => array(),
                'HasBadUrl' => array('break' => false),
                'HasBadEmail' => array(),
                'StringLength' => array('start' => 0, 'stop' => 100),
                'Advert/StopWords' => array(),
                'ProfanityWords' => array(),
            )
        ),

        'hash' => array(
            'db_element' => true,
            'db_field_name' => 'advert_hash',
            'validators' => array()
        ),

        'text' => array(
            'db_element' => true,
            'db_field_name' => 'advert_text',
            'default_value' => null,
            'validators' => array(
                'IsNotEmpty' => array(),
                'HasBadUrl' => array('break' => false),
                'HasBadEmail' => array(),
                'Advert/StopWords' => array(),
                'ProfanityWords' => array(),
            )
        ),

        'price' => array(
            'db_element' => true,
            'db_field_name' => 'advert_price',
            'default_value' => null,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'free' => array(
            'db_element' => true,
            'db_field_name' => 'advert_free',
            'default_value' => 0,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'price_type' => array(
            'type' => 'Krugozor_Module_Advert_Type_PriceType',
            'db_element' => true,
            'db_field_name' => 'advert_price_type',
            'default_value' => 'rur',
            'validators' => array(
                'IsNotEmpty' => array(),
                'VarEnum' => array('enum' => array('RUB', 'EUR', 'USD')),
            )
        ),

        'email' => array(
            'type' => 'Krugozor_Type_Email',
            'db_element' => true,
            'db_field_name' => 'advert_email',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
                'Email' => array(),
            )
        ),

        'phone' => array(
            'db_element' => true,
            'db_field_name' => 'advert_phone',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
            )
        ),

        'icq' => array(
            'db_element' => true,
            'db_field_name' => 'advert_icq',
            'default_value' => null,
            'validators' => array(
                'Decimal' => array('signed' => true),
                'IntRange' => array('min' => 10000, 'max' => Krugozor_Validator_IntRange::PHP_MAX_INT_32)
            )
        ),

        'url' => array(
            'type' => 'Krugozor_Type_Url',
            'db_element' => true,
            'db_field_name' => 'advert_url',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
                'Url' => array(),
            )
        ),

        'skype' => array(
            'db_element' => true,
            'db_field_name' => 'advert_skype',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::MD5_MAX_LENGTH),
            )
        ),

        'user_name' => array(
            'db_element' => true,
            'db_field_name' => 'advert_user_name',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => Krugozor_Validator_StringLength::VARCHAR_MAX_LENGTH),
                'ProfanityWords' => array(),
            )
        ),

        'main_email' => array(
            'db_element' => true,
            'db_field_name' => 'advert_main_email',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'main_phone' => array(
            'db_element' => true,
            'db_field_name' => 'advert_main_phone',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'main_icq' => array(
            'db_element' => true,
            'db_field_name' => 'advert_main_icq',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'main_url' => array(
            'db_element' => true,
            'db_field_name' => 'advert_main_url',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'main_skype' => array(
            'db_element' => true,
            'db_field_name' => 'advert_main_skype',
            'default_value' => 1,
            'validators' => array(
                // skype ограничивает логин 32 символами
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'main_user_name' => array(
            'db_element' => true,
            'db_field_name' => 'advert_main_user_name',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'place_country' => array(
            'db_element' => true,
            'db_field_name' => 'advert_place_country',
            'default_value' => 0,
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'place_region' => array(
            'db_element' => true,
            'db_field_name' => 'advert_place_region',
            'default_value' => 0,
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'place_city' => array(
            'db_element' => true,
            'db_field_name' => 'advert_place_city',
            'default_value' => 0,
            'validators' => array(
                'Empty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'create_date' => array(
            'type' => 'Krugozor_Type_Datetime',
            'db_element' => true,
            'db_field_name' => 'advert_create_date',
            'default_value' => 'now',
            'record_once' => true,
            'validators' => array(
                'DateCorrect' => array('format' => Krugozor_Type_Datetime::FORMAT_DATETIME),
            )
        ),

        'edit_date' => array(
            'type' => 'Krugozor_Type_Datetime',
            'db_element' => true,
            'db_field_name' => 'advert_edit_date',
            'default_value' => null,
            'validators' => array(
                'DateCorrect' => array('format' => Krugozor_Type_Datetime::FORMAT_DATETIME),
            )
        ),

        'vip_date' => array(
            'type' => 'Krugozor_Type_Datetime',
            'db_element' => true,
            'db_field_name' => 'advert_vip_date',
            'default_value' => null,
            'validators' => array(
                'DateCorrect' => array('format' => Krugozor_Type_Datetime::FORMAT_DATETIME),
            )
        ),

        'special_date' => array(
            'type' => 'Krugozor_Type_Datetime',
            'db_element' => true,
            'db_field_name' => 'advert_special_date',
            'default_value' => null,
            'validators' => array(
                'DateCorrect' => array('format' => Krugozor_Type_Datetime::FORMAT_DATETIME),
            )
        ),

        // fake-свойство, заполняется из SQL (показатель, что это vip-объявление)
        'is_vip' => array(
            'db_element' => false,
            'default_value' => 0
        ),

        // fake-свойство, заполняется из SQL (показатель, что это vip-объявление)
        'is_special' => array(
            'db_element' => false,
            'default_value' => 0
        ),

        // fake-свойство, заполняется из SQL (релевантность по запросу)
        'score' => array(
            'db_element' => false,
            'default_value' => 0
        ),

        'view_count' => array(
            'db_element' => true,
            'db_field_name' => 'advert_view_count',
            'default_value' => 0,
            'record_once' => true,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'was_moderated' => array(
            'db_element' => true,
            'db_field_name' => 'advert_was_moderated',
            'default_value' => 0,
            'record_once' => true,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        // Количество изображений объявления.
        // Заполняется с помощью триггера на таблице `thumbnail_advert`.
        'thumbnail_count' => array(
            'db_element' => true,
            'db_field_name' => 'advert_thumbnail_count',
            'default_value' => 0,
            'record_once' => true,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        // Имя главного изображения.
        // Заполняется с помощью триггера на таблице `thumbnail_advert`.
        'thumbnail_file_name' => array(
            'db_element' => true,
            'db_field_name' => 'advert_thumbnail_file_name',
            'default_value' => null,
            'record_once' => true,
            'validators' => array(
                'Md5FileName' => array()
            )
        ),

        // Оплачено объявление или нет
        'payment' => array(
            'db_element' => true,
            'db_field_name' => 'advert_payment',
            'default_value' => 0,
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),
    );

    /**
     * Список объектов изображений типа Krugozor_Module_Advert_Model_Thumbnail,
     * привязанных к данному объявлению.
     *
     * @var Krugozor_Cover_Array
     */
    protected $thumbnails;

    /**
     * @var Krugozor_Module_Robokassa_Service_Robokassa
     */
    private $robokassa;

    /**
     * @var Krugozor_Module_W1_Service_W1
     */
    private $w1;

    /**
     * @var Krugozor_Module_Freekassa_Service_Freekassa
     */
    private $freekassa;

    /**
     * Явный метод для скрытой генерации хэша объявления и расстановки пробелов после знаков пунктуации
     * в тексте сообщения.
     *
     * @param string $text
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function setText($text)
    {
        $this->setHashString($text);
        $this->text = $text;

        return $this;
    }

    /**
     * explicit method для $this->header
     *
     * @param string $header
     * @return string
     */
    public function _setHeader($header)
    {
        $header = Krugozor_Helper_Format::spaceAfterPunctuation($header);

        return mb_ucfirst($header);
    }

    /**
     * Инвертирует активность объявления.
     *
     * @param void
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function invertActive()
    {
        $this->setActive((int)!$this->getActive());

        return $this;
    }

    /**
     * Возвращает объект DateInterval, указывающий сколько осталось до-
     * или уже прошло времени после- времени create_date + $hour часов.
     *
     * @access public
     * @param int $hour колчество часов
     * @return DateInterval
     */
    public function getExpireRestrictionUpdateCreateDate($hour = 1)
    {
        $interval = new DateInterval('PT60M');
        $t_date = clone $this->getCreateDate();
        $t_date->add($interval);

        $now = new Krugozor_Type_Datetime();
        return $now->diff($t_date);
    }

    /**
     * Устанавливает свойство create_date в значение
     * текущего времени - 1 секунда.
     *
     * @access public
     * @param void
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function setCurrentCreateDateDiffSecond()
    {
        $now = new Krugozor_Type_Datetime();
        $now->setTimestamp(time() - 1);
        $this->setCreateDate($now);

        return $this;
    }

    /**
     * Проставляет дату для VIP объявления на $days дней.
     *
     * @param void
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function setVipStatus($days = 14)
    {
        $time = new Krugozor_Type_Datetime();
        $time->add(new DateInterval('P' . $days . 'D'));
        $this->setVipDate($time);

        return $this;
    }

    /**
     * Проставляет дату для Special объявления на $days дней.
     *
     * @param void
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function setSpecialStatus($days = 14)
    {
        $time = new Krugozor_Type_Datetime();
        $time->add(new DateInterval('P' . $days . 'D'));
        $this->setSpecialDate($time);

        return $this;
    }

    /**
     * Возвращает true, если это объявление принадлежит пользователю $user.
     *
     * @param Krugozor_Module_User_Model_User $user
     * @return bool
     */
    public function belongToUser(Krugozor_Module_User_Model_User $user)
    {
        return ($user->getUniqueCookieId() == $this->getUniqueUserCookieId() or $this->belongToRegisterUser($user));
    }

    /**
     * Возвращает true, если это объявление принадлежит зарегестрированному пользователю $user.
     *
     * @param Krugozor_Module_User_Model_User $user
     * @return bool
     */
    public function belongToRegisterUser(Krugozor_Module_User_Model_User $user)
    {
        return (!$user->isGuest() && $user->getId() == $this->getIdUser());
    }

    /**
     * Метод получения объекта Krugozor_Cover_Array с одним элементом - объектом главного изображения,
     * котрое находится на основании денормализованных данных в таблице advert (поле advert_thumbnail_file_name).
     * Если изображения у объявления нет, то будет возвращён объект Krugozor_Cover_Array без элементов.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getDenormalizationThumbnailsList()
    {
        if (!$this->thumbnails) {
            $this->thumbnails = new Krugozor_Cover_Array();

            if ($this->getThumbnailFileName()) {
                $thumbnail = new Krugozor_Module_Advert_Model_Thumbnail();
                $thumbnail->setData(array('file_name' => $this->getThumbnailFileName()));

                $this->thumbnails->append($thumbnail);
            }
        }

        return $this->thumbnails;
    }

    /**
     * Получет и возвращает список объектов изображений, закреплённых за этим объявлением.
     * Lazy Load.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function getThumbnailsList()
    {
        if (!$this->thumbnails) {
            $this->thumbnails = $this->getId()
                ? $this->getMapperManager()->getMapper('Advert/Thumbnail')->findByAdvert($this)
                : new Krugozor_Cover_Array();
        }

        return $this->thumbnails;
    }

    /**
     * Возвращает список объектов изображений,
     * загруженных на основе массива их идентификаторов.
     * Lazy Load.
     * Метод исключительно для формы добавления изображений, когда в виду ошибочного POST-запроса
     * в сценарий приходят ID's уже загруженных для этой сущности изображений.
     *
     * @param void
     * @return Krugozor_Cover_Array
     */
    public function loadThumbnailsListByIds(Krugozor_Cover_Array $ids)
    {
        if (!$this->thumbnails) {
            $this->thumbnails = new Krugozor_Cover_Array();

            foreach ($ids as $id) {
                $thumbnail = $this->getMapperManager()->getMapper('Advert/Thumbnail')->findModelById($id);

                if ($thumbnail->getId()) {
                    $this->thumbnails->append($thumbnail);
                }
            }
        }

        return $this->thumbnails;
    }

    /**
     * Связывает запись об изображениях $this->thumbnail с данным объявлением.
     *
     * @param void
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function saveThumbnails()
    {
        if (is_object($this->thumbnails) && $this->thumbnails instanceof Krugozor_Cover_Array && $this->thumbnails->count()) {
            foreach ($this->thumbnails as $thumbnail) {
                if ($thumbnail instanceof Krugozor_Module_Advert_Model_Thumbnail) {
                    $this->getMapperManager()->getMapper('Advert/Thumbnail')->updateByAdvert($thumbnail, $this);
                }
            }
        }

        return $this;
    }

    /**
     * Отвязвыает все изображения объявления.
     *
     * @param void
     * @return Krugozor_Module_Advert_Model_Advert
     */
    public function deleteThumbnails()
    {
        foreach ($this->getThumbnailsList() as $thumbnail) {
            $this->getMapperManager()->getMapper('Advert/Thumbnail')->unlink($thumbnail);
        }

        return $this;
    }

    /**
     * Дата последней модификации документа для протокола HTTP.
     *
     * @param void
     * @return Krugozor_Type_Datetime
     */
    public function getLastModifiedDate()
    {
        if ($this->getEditDate() !== null && $this->getEditDate() > $this->getCreateDate()) {
            return $this->getEditDate();
        }

        return $this->getCreateDate();
    }

    /**
     * Возвращает объект системы оплаты.
     *
     * @return object
     */
    public function getMerchant()
    {
        return $this->getRobokassaInstance();
    }

    /**
     * Создёт хэш объявления на основании текста объявления.
     *
     * @param $string
     * @return Krugozor_Module_Advert_Model_Advert
     */
    protected function setHashString($string)
    {
        preg_match_all(self::$text_hash_pattern, $string, $matches);
        $this->setHash(md5(implode('', $matches[0])));

        return $this;
    }

    /**
     * @return Krugozor_Module_Robokassa_Service_Robokassa
     */
    private function getRobokassaInstance()
    {
        if (!$this->robokassa) {
            $this->robokassa = new Krugozor_Module_Robokassa_Service_Robokassa();
            $this->robokassa->setAdvert($this);
        }

        return $this->robokassa;
    }

    /**
     * @return Krugozor_Module_Freekassa_Service_Freekassa
     */
    private function getFreekassaInstance()
    {
        if (!$this->freekassa) {
            $this->freekassa = new Krugozor_Module_Freekassa_Service_Freekassa();
            $this->freekassa->setAdvert($this);
        }

        return $this->freekassa;
    }
}