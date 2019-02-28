<?php

namespace Krugozor\Framework;

use Krugozor\Framework\Http\Response;
use Krugozor\Framework\Mapper\CommonMapper;
use Krugozor\Framework\Mapper\Manager;
use Krugozor\Framework\Module\User\Model\User;
use Krugozor\Framework\Http\Request;

abstract class Controller
{
    /**
     * Имя класса представления по умолчанию.
     * Если необходимо задать иное имя класса представления, то оно задается вторым аргументом
     * метода $this->getView() или явно в классе-наследнике.
     *
     * @var string
     */
    protected $default_view_class_name = 'Krugozor\Framework\View';

    /**
     * Объект представления.
     *
     * @var View
     */
    private $view;

    /**
     * Объект текущего пользователя.
     *
     * @var User
     */
    private $current_user;

    /**
     * Звёздный объект-хранилище, содержащий все основные объекты системы.
     *
     * @var Context
     */
    private $context;

    /**
     * Менеджер Мэпперов.
     *
     * @var Manager
     */
    private $mapperManager;

    /**
     * Основной рабочий метод любого конкретного класcа контроллера.
     *
     * @abstract
     * @return mixed
     */
    abstract public function run();

    /**
     * @param Context
     */
    public function __construct(Context $context)
    {
        $this->context = $context;

        $auth = new Authorization($this->getRequest(), $this->getResponse());
        $auth->processSettingsUniqueCookieId($this->getCurrentUser());

        if ($notif = $this->getRequest()->getRequest('notif', 'decimal')) {
            $notification = new Notification($this->context->getDatabase());
            $notification->findById($notif);

            if ($notification->getId()) {
                $this->getView()->setNotification($notification);
            }
        }
    }

    /**
     * Возвращает объект запроса.
     *
     * @return Request
     */
    protected final function getRequest(): Request
    {
        return $this->context->getRequest();
    }

    /**
     * Возвращает объект ответа.
     *
     * @return Response
     */
    protected final function getResponse(): Response
    {
        return $this->context->getResponse();
    }

    /**
     * Создает новый объект системного уведомления.
     *
     * @param int $type тип, один из типов Notification::TYPE_*
     * @return Notification
     */
    protected final function createNotification($type = Notification::TYPE_NORMAL): Notification
    {
        return (new Notification($this->context->getDatabase()))->setType($type);
    }

    /**
     * @param string
     * @return CommonMapper
     */
    protected final function getMapper(string $path): Mapper
    {
        if ($this->mapperManager === null) {
            $this->mapperManager = new Manager($this->context->getDatabase());
        }

        return $this->mapperManager->getMapper($path);
    }

    /**
     * Возвращает объект текущего пользователя.
     *
     * @return User
     */
    protected final function getCurrentUser(): User
    {
        if ($this->current_user === null) {
            $auth = new Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
            $this->current_user = $auth->processAuthentication();
        }

        return $this->current_user;
    }

    /**
     * Проверяет доступ текущего пользователя на доступ к контроллеру $controller_key модуля $module_key.
     * Если $controller_key или $module_key не указаны, берутся текущие модуль и/или контроллер.
     *
     * @param null|string $module_key
     * @param null|string $controller_key
     * @return bool
     */
    protected final function checkAccess(?string $module_key = null, ?string $controller_key = null): bool
    {
        $module_key = $module_key ?: $this->getRequest()->getModuleName()->getCamelCaseStyle();
        $controller_key = $controller_key ?: $this->getRequest()->getControllerName()->getCamelCaseStyle();

        return $this->current_user->checkAccesses($module_key, $controller_key);
    }

    /**
     * Возвращает объект представления.
     * Если представление ещё не создано, оно создается на основе двух параметров -
     * имени файла шаблона и имени класса представления.
     *
     * @param null|sting $template Имя файла шаблона или null если использовать шаблон по имени контроллера.
     *                             Шаблоны ищутся исключительно в рамках текущего модуля и менять это поведение не нужно.
     * @param null|sting $view_class_name Имя файла класса представления или null если использовать класс вида по умолчанию.
     * @return View
     */
    protected function getView(?string $template = null, ?string $view_class_name = null): View
    {
        if ($this->view === null) {
            try {
                if ($view_class_name) {
                    // Дергаем __autoload
                    class_exists($view_class_name);
                }
            } catch (\Exception $e) {
                throw new \RuntimeException(
                    __METHOD__ . ': Не найден класс вида ' . $view_class_name . ' для контроллера ' . get_class($this)
                );
            }

            $view_class_name = $view_class_name ?: $this->default_view_class_name;
            $this->view = new $view_class_name($this->getRealLocalTemplatePath($template));
            $this->view->setDebugInfoFlag(Registry::getInstance()->DEBUG['ENABLED_DEBUG_INFO']);
        }

        return $this->view;
    }

    /**
     * Определяет полный путь к локальному файлу шаблона, находящегося в рамках текущего модуля.
     *
     * Если парметр $template не определен, то файл шаблона ищется в
     * Krugozor/Module/ТекущийМодуль/Template/ТекущийКонтроллер.*
     * Если параметр $template определен, то файл шаблона ищется в
     * Krugozor/Module/ТекущийМодуль/Template/$template.*
     *
     * @param null|sting $template имя файла шаблона или NULL если использовать шаблон текущего контроллера
     * @return null|string
     */
    protected final function getRealLocalTemplatePath(?string $template = null): ?string
    {
        $anchor = Application::getAnchor($this->getRequest()->getModuleName()->getUriStyle());

        if ($template === null) {
            $template_file_paths = [
                $anchor::getpath(), 'Template', $this->getRequest()->getControllerName()->getCamelCaseStyle()
            ];
        } else {
            $template_file_paths = [$anchor::getpath(), 'Template', $template];
        }

        foreach (array('.phtml', '.mail') as $ext) {
            $file = implode(DIRECTORY_SEPARATOR, $template_file_paths) . $ext;

            if (file_exists($file)) {
                return $file;
            }
        }

        // Тут шаблон явно указан и файл не найден - это ошибка.
        if ($template !== null) {
            throw new Exception(__CLASS__ . ': Не найден шаблон ' . $template . '.*');
        }

        // Тут шаблон не указан и не найден - представление не нуждается в шаблоне.
        // Данная ситуация нужна, например, для контроллера, который что-то делает
        // и возвращает в Application только объект Notification.
        return null;
    }

    /**
     * Запись в лог-файл сообщения об ошибке.
     *
     * @param string $message
     * @return bool
     */
    protected function log(string $message): bool
    {
        if (!$message) {
            return false;
        }

        return error_log($message, 0);
    }
}