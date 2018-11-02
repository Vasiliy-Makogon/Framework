<?php

namespace Krugozor\Framework\Module\Getpassword\Service;

use Krugozor\Framework\Service\SendMailWithHash;
use Krugozor\Framework\Module\Getpassword\Mapper\Getpassword as GetpasswordMapper;

/**
 * Сервис отправки писем с целю восстановления пароля пользователя.
 */
class GetpasswordService extends SendMailWithHash
{
    /**
     * @var GetpasswordMapper
     */
    protected $getpassword_mapper;

    /**
     * @param GetpasswordMapper $getpassword_mapper
     * @return GetpasswordService
     */
    public function setGetpasswordMapper(GetpasswordMapper $getpassword_mapper): self
    {
        $this->getpassword_mapper = $getpassword_mapper;

        return $this;
    }

    /**
     * @return bool
     */
    public function sendEmailWithHash(): bool
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
            throw new \InvalidArgumentException(
                __METHOD__ . ': Не могу отправить письмо, отсутствует email-адрес пользователя ' . $this->user->getId()
            );
        }
    }

    /**
     * @param string $hash хэш из запроса
     * @return bool
     */
    public function isValidHash(string $hash): bool
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
     * @param string|null $new_password новый пароль пользователя
     * @throws InvalidArgumentException
     * @return bool
     */
    public function sendMailWithNewPassword(?string $new_password = null)
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
            throw new \InvalidArgumentException(
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
    protected static function createPassword(int $length = 7): string
    {
        return substr(md5(uniqid(rand(), 1)), 0, $length);
    }
}