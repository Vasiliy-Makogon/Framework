<?php
/**
 * Сервис отправки писем с целю восстановления пароля пользователя.
 */
class Krugozor_Module_Getpassword_Service_Getpassword extends Krugozor_Service_SendMailWithHash
{
    /**
     * @var Krugozor_Module_Getpassword_Mapper_Getpassword
     */
    protected $getpassword_mapper;

    /**
     * Принимает mapper-объект Getpassword.
     *
     * @param Krugozor_Module_Getpassword_Mapper_Getpassword $getpassword_mapper
     * @return Krugozor_Module_Getpassword_Service_Getpassword
     */
    public function setGetpasswordMapper(Krugozor_Module_Getpassword_Mapper_Getpassword $getpassword_mapper)
    {
        $this->getpassword_mapper = $getpassword_mapper;

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
            $this->mail->hash = md5($this->user->getLogin() . uniqid(rand(), 1));

            $getpassword = $this->getpassword_mapper->createModel();
            $getpassword->setUserId($this->user->getId());
            $getpassword->setHash($this->mail->hash);

            $this->getpassword_mapper->saveModel($getpassword);

            return $this->mail->send();
        } else {
            throw new Exception(
                __METHOD__ . ': Не могу отправить письмо, отсутствует email-адрес пользователя ' . $this->user->getId()
            );
        }
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Service_SendMailWithHash::isValidHash()
     */
    public function isValidHash($hash)
    {
        $getpassword = $this->getpassword_mapper->findByHash($hash);

        if ($getpassword->getId()) {
            $this->user = $this->user_mapper->findModelById($getpassword->getUserId());

            if ($this->user->getId()) {
                $this->getpassword_mapper->deleteById($getpassword->getId());

                return true;
            }
        }

        return false;
    }

    /**
     * Меняет пароль у пользователя, отсылает новый пароль ему на email.
     *
     * @param string|null новый пароль пользователя
     * @return bool
     */
    public function sendMailWithNewPassword($new_password=null)
    {
        $this->checkMailObjectInstance();
        $this->checkUserObjectInstance();

        $new_password = $new_password === null || !is_scalar($new_password)
                        ? self::createPassword()
                        : $new_password;

        $this->user->setPasswordAsMd5($new_password);

        $this->user_mapper->saveModel($this->user);

        if ($this->user->getEmail()->getValue()) {
            $this->mail->setTo($this->user->getEmail()->getValue());
            $this->mail->user = $this->user;
            $this->mail->new_password = $new_password;

            return $this->mail->send();
        } else {
            throw new Exception(
                __METHOD__ . ': Не могу отправить письмо, отсутствует email-адрес пользователя ' . $this->user->getId()
            );
        }
    }

    /**
     * Создает строку состоящую из символов в диапазоне 0-9a-z
     * длинной $length
     *
     * @param int длинна строки
     * @return string
     */
    public static function createPassword($length=7)
    {
        return substr(md5(uniqid(rand(), 1)), 0, $length);
    }
}