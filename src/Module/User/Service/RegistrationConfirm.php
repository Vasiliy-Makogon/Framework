<?php

/**
 *
 * Пример использования.
 *
 * Посыл уникальной ссылки с хэшем:
 *
 * $mail = new Krugozor_Mail();
 * $mail->setFrom(...);
 * $mail->setReplyTo(...);
 * $mail->setTemplate(...);
 *
 * try
 * {
 * $service = new Krugozor_Module_User_Service_RegistrationConfirm($this->getMapper('User/User'));
 * $service->setUser($this->user)
 * ->setMail($mail)
 * ->setUserPassword($_POST['qwerty'])
 * ->sendEmailWithHash();
 * ...
 * Проверка хэша из письма и отправка нового письма, с паролем и прочими данными для авторизации:
 *
 * $service = new Krugozor_Module_User_Service_RegistrationConfirm($this->getMapper('User/User'));
 *
 * if ($service->isValidHash($_GET['hash']))
 * {
 * $mail = new Krugozor_Mail();
 * $mail->setFrom(...);
 * $mail->setReplyTo(...);
 * $mail->setTemplate(...);
 *
 * $service->setMail($mail)->sendMailWithAuthorizationData();
 *
 * ...
 * @todo: разобраться, как это работает и применить!
 */
class Krugozor_Module_User_Service_RegistrationConfirm extends Krugozor_Service_SendMailWithHash
{
    /**
     * Пароль пользователя, который сохраняет свое "явное состояние" (т.е. не захэширован).
     *
     * @var string
     */
    private $user_password;

    /**
     * Устанавливает пароль пользователя в явном виде, который будет записан в БД (в таблицу registration_confirm)
     * на время, пока пользователь не перешел по уникальной ссылке. После того, как пользователь перейдет
     * по уникальной ссылке, запись с паролем будет удалена, а пароль пользователя
     * отправлен пользователю на email с прочей регистрационной информацией.
     *
     * @param string
     * @return Module_User_Service_RegistrationConfirm
     */
    public function setUserPassword($user_password)
    {
        $this->user_password = $user_password;

        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_SendMailWithHash::sendEmailWithHash()
     */
    public function sendEmailWithHash()
    {
        $this->checkMailObjectInstance();
        $this->checkUserObjectInstance();

        if ($this->user->getEmail()->getValue()) {
            $this->mail->setTo($this->user->getEmail()->getValue());
            $this->mail->user = $this->user;
            $this->mail->hash = md5($this->user->getId() . uniqid(rand(), 1));

            $sql = 'INSERT INTO
                        `registration_confirm`
                     SET
                        `user_id` = ?i, `user_password` = "?s", `hash` = "?s"';

            $this->user_mapper->getDb()->query($sql, $this->user->getId(), $this->user_password, $this->mail->hash);

            return $this->mail->send();
        } else {
            throw new Exception(__CLASS__ . ' не может отправить письмо, отсутствует email-адрес пользователя ' . $this->user->getId());
        }
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_SendMailWithHash::isValidHash()
     */
    public function isValidHash($hash)
    {
        $sql = 'SELECT `user_id`, `user_password` FROM `registration_confirm` WHERE `hash` = "?s" LIMIT 1';

        $res = $this->user_mapper->getDb()->query($sql, $hash);

        if (is_object($res) && $res->getNumRows() === 1) {
            $data = $res->fetch_assoc();

            $this->user = $this->user_mapper->findModelById($data['user_id']);
            $this->user_password = $data['user_password'];

            $this->user_mapper->getDatabase()->query('DELETE FROM `registration_confirm` WHERE `hash` = "?s"', $hash);

            return true;
        }

        return false;
    }

    /**
     * Отправляет письмо пользователю с его данными для авторизации.
     *
     * @param void
     * @return boolean
     */
    public function sendMailWithAuthorizationData()
    {
        $this->checkMailObjectInstance();
        $this->checkUserObjectInstance();

        $this->mail->setTo($this->user->getEmail()->getValue());
        $this->mail->user = $this->user;
        $this->mail->password = $this->user_password;

        return $this->mail->send();
    }

    public function getUser()
    {
        return $this->user;
    }
}