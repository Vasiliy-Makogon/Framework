<?php

namespace Krugozor\Framework;

use Krugozor\Framework\Http\Request;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Module\User\Mapper\User as UserMapper;
use Krugozor\Framework\Type\Datetime;

/**
 * Авторизация, Аутентификация и выход из системы пользователя.
 */
class Authorization
{
    /**
     * Авторизация на год.
     *
     * @var int
     */
    const AUTHORIZATION_ON_YEAR = 360;

    /**
     * Имя cookie с ID пользователя.
     *
     * @var string
     */
    const ID_COOKIE_NAME = 'auth_id';

    /**
     * Имя cookie с хэшем пользователя.
     *
     * @var string
     */
    const HASH_COOKIE_NAME = 'auth_hash';

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Response
     */
    protected $response;

    /**
     * @var UserMapper
     */
    protected $mapper;

    /**
     * @param Request $request
     * @param Response $response
     * @param UserMapper $mapper
     */
    public function __construct(Request $request,
                                Response $response,
                                UserMapper $mapper = null)
    {
        $this->request = $request;
        $this->response = $response;
        $this->mapper = $mapper;
    }

    /**
     * Ищет пользователя по логину и паролю.
     * Если пользователь найден, выставляются куки в response и возвращает true.
     * В обратном случае - false.
     *
     * Куки представляют собой
     * - Идентификатор пользователя в СУБД
     * - Хэш md5 от связки логин_пользователя + хэш_пароля + соль
     *
     * @param string $login логин
     * @param string $password пароль
     * @param int $days время жизни куки в днях
     * @return bool
     */
    public function processAuthorization(string $login, string $password, int $days = 0): bool
    {
        $user = $this->mapper->findByLoginPassword($login, $password);

        if ($user->getId() > 0) {
            $days = (int) $days;
            $time = $days ? time() + 60 * 60 * 24 * $days : 0;

            $this->response->setCookie(
                self::ID_COOKIE_NAME,
                $user->getId(),
                $time,
                '/',
                '',
                true,
                true
            );

            $this->response->setCookie(
                self::HASH_COOKIE_NAME,
                md5($user->getLogin() . $user->getPassword() . Registry::getInstance()->SECURITY['AUTHORIZATION_SALT']),
                $time,
                '/',
                '',
                true,
                true
            );

            return true;
        }

        return false;
    }

    /**
     * Устанавливает cookie с уникальными ID для идентификации зарегистрированных и незарегистрированных пользователей.
     *
     * @param User $user
     */
    public function processSettingsUniqueCookieId(User $user)
    {
        if (!$this->request->getCookie(User::UNIQUE_USER_COOKIE_ID_NAME, 'string')) {
            $this->response->setCookie(
                User::UNIQUE_USER_COOKIE_ID_NAME,
                $user->getUniqueCookieId(),
                $user->getUniqueUserCookieIdLifetime(),
                '/',
                '',
                true,
                true
            );
        } else {
            $user->setUniqueCookieId(
                $this->request->getCookie(User::UNIQUE_USER_COOKIE_ID_NAME, 'string')
            );
        }
    }

    /**
     * Аутентификация пользователя на основании данных из COOKIE.
     *
     * @return User
     */
    public function processAuthentication()
    {
        if ($this->request->getCookie(self::ID_COOKIE_NAME, 'string') &&
            $this->request->getCookie(self::HASH_COOKIE_NAME, 'string')
        ) {
            $user = $this->mapper->findModelById($this->request->getCookie(self::ID_COOKIE_NAME, 'string'));
            $hash = md5($user->getLogin() . $user->getPassword() . Registry::getInstance()->SECURITY['AUTHORIZATION_SALT']);

            if (is_object($user) && $hash === $this->request->getCookie(self::HASH_COOKIE_NAME, 'string')) {
                $this->response->setCookie(
                    User::UNIQUE_USER_COOKIE_ID_NAME,
                    $user->getUniqueCookieId(),
                    $user->getUniqueUserCookieIdLifetime(),
                    '/',
                    '',
                    true,
                    true
                );

                $user->setVisitdate(new Datetime());
                $user->setIp($_SERVER['REMOTE_ADDR']);
                $this->mapper->saveModel($user);

                return $user;
            } else {
                $this->logout();
            }
        }

        return $this->mapper->findModelById(-1);
    }

    /**
     * Уничтожает сеанс (COOKIE) текущего пользователя.
     */
    public function logout()
    {
        $time = time() - 60 * 60 * 24 * 31;

        $this->response->setCookie(
            self::ID_COOKIE_NAME,
            '',
            $time,
            '/',
            '',
            true,
            true
        );

        $this->response->setCookie(
            self::HASH_COOKIE_NAME,
            '',
            $time,
            '/',
            '',
            true,
            true
        );
    }
}