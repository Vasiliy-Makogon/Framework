<?php

abstract class Krugozor_Controller
{
    /**
     * Имя класса представления по умолчанию.
     * Если необходимо задать иное имя класса представления, то оно задается вторым аргументом
     * метода $this->getView() или явно в классе-наследнике.
     *
     * @var string
     */
    protected $default_view_class_name = 'Krugozor_View';

    /**
     * Объект представления.
     *
     * @var Krugozor_View
     */
    private $view;

    /**
     * Объект текущего пользователя.
     *
     * @var Krugozor_Module_User_Model_User
     */
    private $current_user;

    /**
     * Звёздный объект-хранилище, содержащий все основные объекты системы.
     *
     * @var Krugozor_Context
     */
    private $context;

    /**
     * Менеджер Мэпперов.
     *
     * @var Krugozor_Mapper_Manager
     */
    private $mapperManager;

    /**
     * Основной рабочий метод любого конкретного класcа контроллера.
     *
     * @abstract
     * @param void
     * @return mixed
     */
    abstract public function run();

    /**
     * @param Krugozor_Context
     */
    public function __construct(Krugozor_Context $context)
    {
        $this->context = $context;

        $auth = new Krugozor_Authorization($this->getRequest(), $this->getResponse());
        $auth->processSettingsUniqueCookieId($this->getCurrentUser());

        // Если в запросе присутствует параметр notif, значит необходимо получить во view
        // информацию, переданную с предыдущей страницы и вывести её в шаблоне.
        if ($notif = $this->getRequest()->getRequest('notif', 'decimal')) {
            $notification = new Krugozor_Notification($this->context->getDatabase());
            $notification->findById($notif);

            if ($notification->getId()) {
                $this->getView()->setNotification($notification);
            }
        }
    }

    /**
     * Возвращает объект запроса.
     *
     * @param void
     * @return Krugozor_Http_Request
     */
    protected final function getRequest()
    {
        return $this->context->getRequest();
    }

    /**
     * Возвращает объект ответа.
     *
     * @param void
     * @return Krugozor_Http_Response
     */
    protected final function getResponse()
    {
        return $this->context->getResponse();
    }

    /**
     * Создает новый объект системного уведомления.
     *
     * @param int $type тип, один из типов Krugozor_Notification::TYPE_*
     * @return Krugozor_Notification
     */
    protected final function createNotification($type = Krugozor_Notification::TYPE_NORMAL)
    {
        return (new Krugozor_Notification($this->context->getDatabase()))->setType($type);
    }

    /**
     * Метод принимает строку вида `ModuleName/MapperName`,
     * и возвращает объект мэппера, экземпляр класса
     * Krugozor_Module_[ModuleName]_Mapper_[MapperName].
     *
     * @param string
     * @return Krugozor_Mapper_Common
     */
    protected final function getMapper($path)
    {
        if ($this->mapperManager === null) {
            $this->mapperManager = new Krugozor_Mapper_Manager($this->context->getDatabase());
        }

        return $this->mapperManager->getMapper($path);
    }

    /**
     * Возвращает объект текущего пользователя.
     *
     * @param void
     * @return Krugozor_Module_User_Model_User
     */
    protected final function getCurrentUser()
    {
        if ($this->current_user === null) {
            $auth = new Krugozor_Authorization($this->getRequest(), $this->getResponse(), $this->getMapper('User/User'));
            $this->current_user = $auth->processAuthentication();
        }

        return $this->current_user;
    }

    /**
     * (non-PHPdoc)
     * @see Krugozor_Module_User_Model_User::checkAccesses()
     */
    protected final function checkAccess($module_key = null, $controller_key = null)
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
     * @return Krugozor_View
     */
    protected function getView($template = null, $view_class_name = null)
    {
        if ($this->view === null) {
            try {
                if ($view_class_name) {
                    // Дергаем __autoload
                    class_exists($view_class_name);
                }
            } catch (Exception $e) {
                throw new RuntimeException(
                    __METHOD__ . ': Не найден класс вида ' . $view_class_name . ' для контроллера ' . get_class($this)
                );
            }

            $view_class_name = $view_class_name ?: $this->default_view_class_name;
            $this->view = new $view_class_name($this->getRealLocalTemplatePath($template));
            $this->view->setDebugInfoFlag(Krugozor_Registry::getInstance()->DEBUG['ENABLED_DEBUG_INFO']);
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
     * @param null|sting имя файла шаблона или NULL если использовать шаблон текущего контроллера
     * @return string|null
     */
    protected final function getRealLocalTemplatePath($template = null)
    {
        if ($template === null) {
            $template_file_paths = array(
                __DIR__, 'Module', $this->getRequest()->getModuleName()->getCamelCaseStyle(), 'Template',
                $this->getRequest()->getControllerName()->getCamelCaseStyle()
            );
        } else {
            $template_file_paths = array(
                __DIR__, 'Module', $this->getRequest()->getModuleName()->getCamelCaseStyle(), 'Template', $template
            );
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
        // Данная ситуация нужна, например, для контроллера, который что-то делает и возвращает в Krugozor_Application
        // только объект Krugozor_Notification.
        return null;
    }

    /**
     * Запись в лог-файл сообщения об ошибке.
     *
     * @param string $message
     * @return bool
     */
    protected function log($message)
    {
        if (!$message) {
            return false;
        }

        return error_log($message, 0);
    }
}