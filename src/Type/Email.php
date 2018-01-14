<?php

/**
 * Тип модели `email`
 */
class Krugozor_Type_Email implements Krugozor_Type_Interface
{
    /**
     * Email адрес.
     *
     * @var string
     */
    protected $email;

    public function __construct($email)
    {
        $this->setValue($email);
    }

    public function getValue()
    {
        return $this->email;
    }

    public function setValue($value)
    {
        $this->email = $value;
    }

    /**
     * Возвращает md5 хэш email-адреса + соль.
     *
     * Что бы предотвратить сбор email-адресов пользователей, с помощью ajax формируется запрос типа
     * /advert/frontend-ajax-get-email/id/79305/hash/75342c398ef420d61a8defe4291332dd
     * где id - ID пользователя, а хэш - результат работы этого метода.
     * Контроллер frontend-ajax-get-email находит пользователя по ID и сравнивает хэш из запроса
     * с хэшем, возвращенным этим методом. Если строки совпадают, то контроллер в качестве ответа
     * отдаёт email адрес пользователя.
     *
     * @param void
     * @return string
     */
    public function getMailHashForAccessView()
    {
        return md5($this->getValue() . Krugozor_Registry::getInstance()->SECURITY['AUTHORIZATION_SALT']);
    }
}