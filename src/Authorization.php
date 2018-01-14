<?php

/**
 * Авторизация, Аутентификация и выход из системы пользователя.
 */
class Krugozor_Authorization
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
     * @var Krugozor_Http_Request
     */
    protected $request;

    /**
     * @var Krugozor_Http_Response
     */
    protected $response;

    /**
     * @var Krugozor_Module_User_Mapper_User
     */
    protected $mapper;

    /**
     * @param Krugozor_Http_Request $request
     * @param Krugozor_Http_Response $response
     * @param Krugozor_Module_User_Mapper_User $mapper
     */
    public function __construct(Krugozor_Http_Request $request,
                                Krugozor_Http_Response $response,
                                Krugozor_Module_User_Mapper_User $mapper = null)
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
     * @param null|int $days время жизни куки в днях
     * @return bool
     */
    public function processAuthorization($login, $password, $days = 0)
    {
        $user = $this->mapper->findByLoginPassword($login, $password);

        if ($user->getId() > 0) {
            $days = (int)$days;
            $time = $days ? time() + 60 * 60 * 24 * $days : 0;

            $this->response->setcookie(self::ID_COOKIE_NAME, $user->getId(), $time, '/');
            $this->response->setcookie(
                self::HASH_COOKIE_NAME,
                md5($user->getLogin() . $user->getPassword() . Krugozor_Registry::getInstance()->SECURITY['AUTHORIZATION_SALT']),
                $time,
                '/'
            );

            return true;
        }

        return false;
    }

    /**
     * Устанавливает cookie с уникальными ID для идентификации зарегистрированных и незарегистрированных пользователей.
     *
     * @param Krugozor_Module_User_Model_User $user
     * @return void
     */
    public function processSettingsUniqueCookieId(Krugozor_Module_User_Model_User $user)
    {
        if (!$this->request->getCookie(Krugozor_Module_User_Model_User::UNIQUE_USER_COOKIE_ID_NAME, 'string')) {
            $this->response->setCookie(
                Krugozor_Module_User_Model_User::UNIQUE_USER_COOKIE_ID_NAME,
                $user->getUniqueCookieId(),
                $user->getUniqueUserCookieIdLifetime(),
                '/'
            );
        } else {
            $user->setUniqueCookieId(
                $this->request->getCookie(Krugozor_Module_User_Model_User::UNIQUE_USER_COOKIE_ID_NAME, 'string')
            );
        }
    }

    /**
     * Аутентификация пользователя на основании данных из COOKIE.
     *
     * @param void
     * @return Krugozor_Module_User_Model_User
     */
    public function processAuthentication()
    {
        if ($this->request->getCookie(self::ID_COOKIE_NAME, 'string') &&
            $this->request->getCookie(self::HASH_COOKIE_NAME, 'string')
        ) {
            $user = $this->mapper->findModelById($this->request->getCookie(self::ID_COOKIE_NAME, 'string'));

            if (is_object($user) &&
                md5($user->getLogin() . $user->getPassword() . Krugozor_Registry::getInstance()->SECURITY['AUTHORIZATION_SALT'])
                === $this->request->getCookie(self::HASH_COOKIE_NAME, 'string')
            ) {
                $user->setVisitdate(new Krugozor_Type_Datetime());
                $user->setIp($_SERVER['REMOTE_ADDR']);
                $this->mapper->saveModel($user);

                // После аутентификации нужно проверить, есть ли у пользователя свойство unique_cookie_id
                // Если есть, то меняем значение уникального ID в cookie на то, что лежит в базе.
                if ($user->unique_cookie_id) {
                    $this->response->setCookie(
                        Krugozor_Module_User_Model_User::UNIQUE_USER_COOKIE_ID_NAME,
                        $user->unique_cookie_id,
                        $user->getUniqueUserCookieIdLifetime(),
                        '/'
                    );
                } // Если у пользователя уникальной куки нет (напрмиер, создали пользователя через админ), то записываем его в базу.
                else {
                    if ($this->request->getCookie(Krugozor_Module_User_Model_User::UNIQUE_USER_COOKIE_ID_NAME, 'string')) {
                        $user->setUniqueCookieId($this->request->getCookie(Krugozor_Module_User_Model_User::UNIQUE_USER_COOKIE_ID_NAME, 'string'));
                        $this->mapper->saveModel($user);
                    }
                }

                return $user;
            } else {
                $this->logout();
            }
        }

        return $this->mapper->findModelById(-1);
    }

    /**
     * Уничтожает сеанс (COOKIE) текущего пользователя.
     *
     * @param void
     * @return void
     */
    public function logout()
    {
        $time = time() - 60 * 60 * 24 * 31;

        $this->response->setCookie(self::ID_COOKIE_NAME, '', $time, '/');
        $this->response->setCookie(self::HASH_COOKIE_NAME, '', $time, '/');
    }
}