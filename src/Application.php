<?php

namespace Krugozor\Framework;

use Krugozor\Framework\Http\Cover\Uri\CanonicalRequestUri;
use Krugozor\Framework\Http\Cover\Uri\PartEntity;
use Krugozor\Framework\Http\Cover\Uri\RequestUri;
use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Module\Captcha\Model\Captcha;
use Krugozor\Framework\Module\Resource\Model\Resource;
use Krugozor\Framework\Statical\Arrays;
use Krugozor\Framework\Statical\Strings;

final class Application
{
    /**
     * Объект-хранилище, содержащий все "звёздные" объекты системы.
     *
     * @var Context
     */
    private $context = null;

    /**
     * Массив допустимых URL-адресов проекта в виде
     * массивов регулярных выражений (см. /config/routes.php).
     *
     * @var array
     */
    private $routes = array();

    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;
    }

    /**
     * Принимает путь к PHP-файлу описания маршрутов URL.
     * Файл должен с помощью конструкции return возвращать массив
     * правил маршрутизации (см. /config/routes.php).
     *
     * @param string $path путь до файла правил маршрутизации
     * @return Application
     */
    final public function setRoutesFromPhpFile(string $path): self
    {
        if (!file_exists($path)) {
            throw new \RuntimeException(__METHOD__ . ': Не найден файл описания маршрутов URL ' . $path);
        }

        $this->setRoutes((array)require $path);

        return $this;
    }

    /**
     * Основной метод приложения, запускающий конкретные контроллеры
     * и отдающий в output результат.
     */
    public function run()
    {
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

        if (!$this->compareRequestWithUriRoutes($uri)) {
            if (!$this->compareRequestWithStandartUriMap($uri)) {
                $this->context->getRequest()->setModuleName(new PartEntity('not-found'));
                $this->context->getRequest()->setControllerName(new PartEntity('not-found'));
                $this->context->getRequest()->setCanonicalRequestUri(new CanonicalRequestUri($uri));
                $this->context->getRequest()->setRequestUri(new RequestUri($_SERVER['REQUEST_URI']));
            }
        }

        $controller_name = $this->getControllerClassName(
            $this->context->getRequest()->getModuleName()->getCamelCaseStyle(),
            $this->context->getRequest()->getControllerName()->getCamelCaseStyle()
        );

        $controller = new $controller_name($this->context);
        $result = $controller->run();

        if (!is_object($result)) {
            throw new \RuntimeException(__METHOD__ . ': Не получен результат от работы контроллера ' . $controller_name);
        }

        switch ($result) {
            case $result instanceof View:
                $result->run();
                $this->context->getResponse()->sendCookie()->sendHeaders();
                $debugKey = Registry::getInstance()->DEBUG['QS_DEBUG_KEY'];
                $trimmer = !(
                    !empty($debugKey) && $this->context->getRequest()->getRequest($debugKey) ||
                    Registry::getInstance()->DEBUG['ENABLED_DEBUG_INFO']
                );
                echo $result->getOutput($trimmer);
                break;

            case $result instanceof Notification:
                $this->context->getResponse()
                    ->setHeader(Response::HEADER_LOCATION, $result->getRedirectUrl())
                    ->sendCookie()
                    ->sendHeaders();
                break;

            case $result instanceof Captcha:
                $this->context->getResponse()->sendHeaders();
                $result->showCaptcha();
                break;

            case $result instanceof Response:
                $this->context->getResponse()->sendCookie()->sendHeaders();
                break;

            case $result instanceof Resource:
                $this->context->getResponse()->sendCookie()->sendHeaders();
                echo $result->getResourceContents();
                break;

            default:
                new \DomainException('Результат от работы контроллера не определён');
        }
    }

    /**
     * Возвращает Anchor-объект модуля $moduleName.
     * @param $moduleName
     * @return Anchor
     */
    final public static function getAnchor($moduleName)
    {
        $anchor = 'Krugozor\\Framework\\Module\\' . Strings::formatToCamelCaseStyle($moduleName) . '\\Anchor';
        if (!class_exists($anchor)) {
            throw new \RuntimeException("Not found Anchor-file at path `$anchor`");
        }

        return new $anchor;
    }

    /**
     * Принимает массив допустимых маршрутов URL.
     *
     * @param array $routes
     * @return Application
     */
    private function setRoutes(array $routes): self
    {
        $this->routes = $routes;

        return $this;
    }

    /**
     * Разбирает текущий URI-запрос, который передается в качестве аргумента,
     * и сравнивает его с одним из паттернов URL-карты $this->routes.
     * Если совпадение найдено, то в объект-оболочку Request записывается информация
     * из карт, такая как:
     * - имя модуля
     * - имя контролера
     * - запрошеный URI-адрес
     * - параметры запроса.
     *
     * @param string URI-запрос
     * @return boolean true если для запроса $uri найдены совпадения в $this->routes
     *                 и false в противном случае.
     */
    private function compareRequestWithUriRoutes(string $uri): bool
    {
        foreach ($this->routes as $map) {
            if (preg_match($map['pattern'], $uri, $params)) {
                array_shift($params);

                foreach ($params as $index => $value) {
                    $this->context->getRequest()->getRequest()->{$map['aliases'][$index]} = $value;
                }

                if (!empty($map['default']) && is_array($map['default'])) {
                    foreach ($map['default'] as $key => $value) {
                        $this->context->getRequest()->getRequest()->{$key} = $value;
                    }
                }

                $this->context->getRequest()->setModuleName(new PartEntity($map['module']));
                $this->context->getRequest()->setControllerName(new PartEntity($map['controller']));
                $this->context->getRequest()->setCanonicalRequestUri(new CanonicalRequestUri($uri));
                $this->context->getRequest()->setRequestUri(new RequestUri($_SERVER['REQUEST_URI']));

                return true;
            }
        }

        return false;
    }

    /**
     * По символу "/" разбирает URI-запрос $uri таким образом,
     * что четное число получившихся при разборе значений образуют пары
     * вида "свойство" => "значение". Данные пары помещаются в Request.
     * Первая пара является именем модуля и именем контроллера.
     * Например URI-запрос вида:
     *
     * /ajax/region/country/155
     *
     * метод распарсит таким образом, что при наличие соответствующего файла
     * и класса, в Request будет помещена информация о текущем модуле Ajax,
     * контроллере Region и переменной запроса country со значением 155.
     *
     * @param string URI-запрос
     * @return boolean true если для запроса $uri найден контроллер
     *                 и false в противном случае.
     */
    private function compareRequestWithStandartUriMap(string $uri): bool
    {
        $uri_parts = explode('/', trim($uri, ' /'));

        $count_params = count($uri_parts);

        if ($count_params % 2) {
            return false;
        }

        for ($i = 0; $i < $count_params; $i++) {
            $params[$uri_parts[$i]] = $uri_parts[++$i];
        }

        $first_element = Arrays::array_kshift($params);
        $module = key($first_element);
        $controller = current($first_element);

        if (class_exists($this->getControllerClassName(
            Strings::formatToCamelCaseStyle($module),
            Strings::formatToCamelCaseStyle($controller)
        ), true)) {
            $this->context->getRequest()->setModuleName(new PartEntity($module));
            $this->context->getRequest()->setControllerName(new PartEntity($controller));
            $this->context->getRequest()->setCanonicalRequestUri(new CanonicalRequestUri($uri));
            $this->context->getRequest()->setRequestUri(new RequestUri($_SERVER['REQUEST_URI']));

            $this->context->getRequest()->getRequest()->setData($params);

            return true;
        } else {
            return false;
        }
    }

    /**
     * Возвращает полное имя класса контроллера.
     *
     * @param string $module имя модуля
     * @param string $controller имя контроллера
     * @return string полное имя класса контроллера
     */
    private function getControllerClassName(string $module, string $controller): string
    {
        return 'Krugozor\\Framework\\Module\\' . ucfirst($module) . '\\Controller\\' . ucfirst($controller);
    }
}