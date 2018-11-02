<?php

namespace Krugozor\Framework\Module\User\Model;

use Krugozor\Framework\Model;
use Krugozor\Framework\Statical\Strings;
use Krugozor\Framework\Type\Datetime;
use Krugozor\Framework\Validator\StringLength;
use Krugozor\Framework\Validator\IntRange;

class User extends Model
{
    /**
     * ID системного пользователя "Гость" в таблице.
     *
     * @var int
     */
    const GUEST_USER_ID = -1;

    /**
     * Время жизни уникального cookie пользователя, в годах.
     *
     * @var int
     */
    const UNIQUE_USER_COOKIE_ID_LIFETIME_YEARS = 10;

    /**
     * Имя cookie гостя
     *
     * @var string
     */
    const UNIQUE_USER_COOKIE_ID_NAME = 'unique_user_cookie_id';

    /**
     * true, если пользователь принадлежит к группе "пользователи"
     *
     * @var boolean
     */
    protected $is_user;

    /**
     * true, если пользователь принадлежит к группе "администраторы"
     *
     * @var boolean
     */
    protected $is_administrator;

    protected static $db_field_prefix = 'user';

    protected static $model_attributes = array
    (
        'id' => array(
            'db_element' => false,
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => true),
            )
        ),

        'unique_cookie_id' => array(
            'db_element' => true,
            'db_field_name' => 'user_unique_cookie_id',
            'record_once' => true,
            'validators' => array(
                'StringLength' => array(
                    'start' => StringLength::MD5_MAX_LENGTH,
                    'stop' => StringLength::MD5_MAX_LENGTH
                ),
                'CharPassword' => array(),
            )
        ),

        'active' => array(
            'db_element' => true,
            'db_field_name' => 'user_active',
            'default_value' => 1,
            'validators' => array(
                'IsNotEmptyString' => array(),
                'Decimal' => array('signed' => false),
                'IntRange' => array('min' => 0, 'max' => 1),
            )
        ),

        'group' => array(
            'db_element' => true,
            'db_field_name' => 'user_group',
            'default_value' => 2, // 2 - ID группы Пользователи
            'validators' => array(
                'IsNotEmpty' => array(),
                'Decimal' => array('signed' => false),
            )
        ),

        'login' => array(
            'db_element' => true,
            'db_field_name' => 'user_login',
            'validators' => array(
                'IsNotEmpty' => array(),
                'StringLength' => array('start' => 0, 'stop' => StringLength::VARCHAR_MAX_LENGTH),
                'CharPassword' => array(),
            )
        ),

        'email' => array(
            'type' => 'Krugozor\\Framework\\Type\\Email',
            'db_element' => true,
            'default_value' => null,
            'db_field_name' => 'user_email',
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => StringLength::VARCHAR_MAX_LENGTH),
                'Email' => array(),
            )
        ),

        'password' => array(
            'db_element' => true,
            'db_field_name' => 'user_password',
            'record_once' => true,
            'validators' => array(
                'CharPassword' => array(),
            )
        ),

        'regdate' => array(
            'type' => 'Krugozor\\Framework\\Type\\Datetime',
            'db_element' => true,
            'db_field_name' => 'user_regdate',
            'default_value' => 'now',
            'record_once' => true,
            'validators' => array(
                'DateCorrect' => array('format' => Datetime::FORMAT_DATETIME),
            )
        ),

        'visitdate' => array(
            'type' => 'Krugozor\\Framework\\Type\\Datetime',
            'db_element' => true,
            'db_field_name' => 'user_visitdate',
            'default_value' => null,
            'validators' => array(
                'DateCorrect' => array('format' => Datetime::FORMAT_DATETIME),
            )
        ),

        'ip' => array(
            'db_element' => true,
            'db_field_name' => 'user_ip',
            'default_value' => null,
            'validators' => array()
        ),

        'first_name' => array(
            'db_element' => true,
            'db_field_name' => 'user_first_name',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => 30),
            )
        ),

        'last_name' => array(
            'db_element' => true,
            'db_field_name' => 'user_last_name',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => 30),
            )
        ),

        'age' => array(
            'type' => 'Krugozor\\Framework\\Type\\Datetime',
            'db_element' => true,
            'db_field_name' => 'user_age',
            'default_value' => null,
            'validators' => array(
                'DateCorrect' => array('format' => Datetime::FORMAT_DATETIME),
            )
        ),

        'sex' => array(
            'type' => 'Krugozor\\Framework\\Module\\User\\Type\\Sex',
            'db_element' => true,
            'db_field_name' => 'user_sex',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 1, 'stop' => 1),
                'VarEnum' => array('enum' => array('M', 'F')),
            )
        ),

        'city' => array(
            'db_element' => true,
            'db_field_name' => 'user_city',
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'region' => array(
            'db_element' => true,
            'db_field_name' => 'user_region',
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'country' => array(
            'db_element' => true,
            'db_field_name' => 'user_country',
            'default_value' => 0,
            'validators' => array(
                'Decimal' => array('signed' => false),
            )
        ),

        'type' => array(
            'db_element' => false,
            'type' => 'Krugozor\\Framework\\Module\\User\\Type\\Type',
            'db_field_name' => 'user_type',
            'default_value' => null,
            'validators' => array(
                'VarEnum' => array('enum' => array('private_person', 'company')),
            )
        ),

        'phone' => array(
            'db_element' => true,
            'db_field_name' => 'user_phone',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => StringLength::VARCHAR_MAX_LENGTH),
            )
        ),

        'icq' => array(
            'db_element' => true,
            'db_field_name' => 'user_icq',
            'default_value' => null,
            'validators' => array(
                'Decimal' => array('signed' => true),
                'IntRange' => array('min' => 10000, 'max' => IntRange::PHP_MAX_INT_32)
            )
        ),

        'url' => array(
            'db_element' => true,
            'type' => 'Krugozor\\Framework\\Type\\Url',
            'db_field_name' => 'user_url',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => StringLength::VARCHAR_MAX_LENGTH),
                'Url' => array()
            )
        ),

        'skype' => array(
            'db_element' => true,
            'db_field_name' => 'user_skype',
            'default_value' => null,
            'validators' => array(
                'StringLength' => array('start' => 0, 'stop' => StringLength::MD5_MAX_LENGTH),
            )
        ),
    );

    /**
     * Проверяет доступ пользователя к контроллеру $controller_key модуля $module_key.
     * Возвращает TRUE, если доступ разрешён и FALSE в противном случае.
     *
     * @param string $module_key
     * @param string $controller_key
     * @return boolean
     */
    public function checkAccesses($module_key, $controller_key)
    {
        $group = $this->getMapperManager()->getMapper('Group/Group')->findModelById($this->getGroup());

        if ($group->getActive() == 0) {
            return false;
        }

        return $group->getDenormalizedAccesses()->checkAccess($module_key, $controller_key)
            &&
            ($this->isGuest() ? true : $this->getActive());
    }

    /**
     * @param array|\Krugozor\Framework\Cover\CoverArray $data
     * @param array $excluded_keys
     * @return $this
     */
    public function setData($data, array $excluded_keys = []): Model
    {
        $object = parent::setData($data, $excluded_keys);

        $data['age_day'] = isset($data['age_day']) ? (int)$data['age_day'] : 0;
        $data['age_month'] = isset($data['age_month']) ? (int)$data['age_month'] : 0;
        $data['age_year'] = isset($data['age_year']) ? (int)$data['age_year'] : 0;

        if ($data['age_day'] && $data['age_month'] && $data['age_year']) {
            $age = Datetime::createFromFormat(
                'j-n-Y H:i:s',
                $data['age_day'] . '-' . $data['age_month'] . '-' . $data['age_year'] . ' 00:00:00'
            );

            $object->setAge($age);
        }

        return $this;
    }

    /**
     * Возвращает true, если пользователь принадлежит к группе "Гости",
     * false в ином случае.
     *
     * @return bool
     */
    public function isGuest()
    {
        return $this->getId() == self::GUEST_USER_ID;
    }

    /**
     * Возвращает true, если пользователь принадлежит к группе "Пользователи",
     * false в ином случае.
     *
     * @return bool
     */
    public function isUser()
    {
        if ($this->is_user === null) {
            $this->is_user = $this->getGroup() == $this->getMapperManager()->getMapper('Group/Group')
                    ->findGroupByAlias('user')
                    ->getId();
        }

        return $this->is_user;
    }

    /**
     * Возвращает true, если пользователь принадлежит к группе "Администраторы",
     * false в ином случае.
     *
     * @return bool
     */
    public function isAdministrator()
    {
        if ($this->is_administrator === null) {
            $this->is_administrator = $this->getGroup() == $this->getMapperManager()->getMapper('Group/Group')
                    ->findGroupByAlias('administrator')
                    ->getId();
        }

        return $this->is_administrator;
    }

    /**
     * Возвращает уникальный ID пользователя.
     * Данный идентификатор ставится в cookie обозревателя пользователя при его первом заходе на сайт.
     * В дальнейшем, если пользователь регестрируется, данный идентификатор пишется в базу и при каждом процессе
     * авторизации/аутентификации достается из базы и также ставится в cookie. Таким образом пользователь всегда
     * опознается, будь он авторизован или нет.
     *
     * @return string
     */
    public function getUniqueCookieId()
    {
        if (!$this->unique_cookie_id) {
            $this->setUniqueCookieId(Strings::getUnique());
        }

        return $this->unique_cookie_id;
    }

    /**
     * Возвращает время жизни уникального ID пользователя в cookie.
     *
     * @return string
     */
    public function getUniqueUserCookieIdLifetime()
    {
        return time() + 60 * 60 * 24 * 365 * self::UNIQUE_USER_COOKIE_ID_LIFETIME_YEARS;
    }

    /**
     * Возвращает полное имя пользователя (имя фамилия).
     *
     * @return string
     */
    public function getFullName()
    {
        return $this->first_name . ($this->last_name ? ' ' . $this->last_name : '');
    }

    /**
     * Возвращает полное имя пользователя или его логин.
     *
     * @return string
     */
    public function getFullNameOrLogin()
    {
        return $this->getFullName() ? $this->getFullName() : $this->getLogin();
    }

    public function getAgeDay()
    {
        if ($this->age && $this->age instanceof Datetime) {
            return $this->age->format('j');
        }

        return null;
    }

    public function getAgeMonth()
    {
        if ($this->age && $this->age instanceof Datetime) {
            return $this->age->format('n');
        }

        return null;
    }

    public function getAgeYear()
    {
        if ($this->age && $this->age instanceof Datetime) {
            return $this->age->format('Y');
        }

        return null;
    }

    /**
     * @see parent::setId()
     */
    public function setId($id): Model
    {
        if (!empty($this->data['id']) && $this->data['id'] != -1 && $this->data['id'] != $id) {
            throw new \LogicException(
                __METHOD__ . ': Попытка переопределить значение ID объекта модели ' . get_class($this) . ' значением ' . $id
            );
        }

        $this->id = $id;

        return $this;
    }

    /**
     * explicit method
     *
     * @param string $url
     * @return string
     */
    protected function _setUrl($url)
    {
        return $url === 'http://' ? null : $url;
    }

    /**
     * Устанавливает пароль пользователя как хэш от строки $password.
     *
     * @param string $password
     * @return $this
     */
    public function setPasswordAsMd5($password)
    {
        $this->setPassword(md5($password));

        return $this;
    }
}