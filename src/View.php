<?php

namespace Krugozor\Framework;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Helper\Form;
use Krugozor\Framework\View\Lang;
use Krugozor\Framework\Http\Request;

class View
{
    /**
     * Хранилище данных, передаваемых контроллером
     * через магические методы __set и __get.
     *
     * @var CoverArray
     */
    protected $data;

    /**
     * Хранилище данных файлов интернационализации.
     *
     * @var Lang
     */
    protected $lang;

    /**
     * Путь до файла шаблона.
     *
     * @var string
     */
    protected $template_file;

    /**
     * Сгенерированный HTML.
     *
     * @var string
     */
    protected $output;

    /**
     * Массив объектов-хелперов, работающих с view.
     *
     * @var array
     */
    protected $helpers = [];

    /**
     * Объект уведомлений.
     *
     * @var Notification
     */
    protected $notification;

    /**
     * Разрешено ли выводить отладочную информацию внизу страницы.
     *
     * @var bool
     */
    private $enabled_debug_info = false;

    /**
     * View constructor.
     * @param null|string $template_file путь до файла шаблона
     */
    public function __construct(?string $template_file = null)
    {
        $this->template_file = $template_file;
        $this->data = new CoverArray();
        $this->lang = new Lang();
    }

    /**
     * Возвращает объект запроса.
     * Это лишь краткая форма записи получения данных запроса из View.
     * Данный метод необъодим, т.к. зачастую в шаблонах необходимо иметь
     * данные о параметрах запроса, URL адресе и т.п.
     *
     * @return Request
     */
    final public function getRequest(): Request
    {
        return Context::getInstance()->getRequest();
    }

    /**
     * Возвращает элемент из хранилища данных $this->data.
     *
     * @return mixed
     */
    final public function __get(string $key)
    {
        return $this->data->$key;
    }

    /**
     * Добавляет новый элемент в хранилище данных $this->data.
     *
     * @param string $key
     * @param $value
     */
    final public function __set(string $key, $value)
    {
        $this->data->$key = $value;
    }

    /**
     * Возвращает объект данных интернационализации.
     *
     * @return Lang
     */
    final public function getLang(): Lang
    {
        return $this->lang;
    }

    /**
     * Возвращает объект-хэлпер $helper_name.
     *
     * @param string $helper_name имя класса-хэлпера
     * @return mixed|object
     */
    final public function getHelper()
    {
        if (!func_num_args()) {
            throw new \InvalidArgumentException(
                'Попытка вызвать метода ' . __METHOD__ . ' без указания класса-помощника'
            );
        }

        $args = func_get_args();
        $helper_name = array_shift($args);

        switch ($helper_name) {
            // Для хэлпера форм указываем шаблон описания ошибок заполнения полей.
            case '\Krugozor\Framework\Helper\Form':
                if (!isset($this->helpers[$helper_name])) {
                    $this->helpers[$helper_name] = Form::getInstance();
                    $this->helpers[$helper_name]->setFieldErrorTemplate(
                        $this->getRealTemplatePath('Local/FieldError')
                    );
                }
                return $this->helpers[$helper_name];

            default:
                if (!class_exists($helper_name)) {
                    throw new \InvalidArgumentException(
                        __METHOD__ . ": Попытка вызвать неизвестный класс-помощник $helper_name"
                    );
                } else {
                    if (!isset($this->helpers[$helper_name])) {
                        $cls = new \ReflectionClass($helper_name);

                        // Если хэлпер Singelton, то сохраняем его в хранилище
                        // иначе - просто инстанцируем, возвращаем и "забываем" о нем.
                        if ($cls->hasMethod('getInstance')) {
                            $method = $cls->getMethod('getInstance');

                            if ($method->isStatic()) {
                                $this->helpers[$helper_name] = call_user_func_array(
                                    array($cls->getName(), 'getInstance'),
                                    $args
                                );
                            }
                        } else {
                            return $cls->newInstanceArgs($args);
                        }
                    }

                    return $this->helpers[$helper_name];
                }
        }
    }

    /**
     * Создаёт и возвращает HTML-код на основании текущего шаблона и данных,
     * присутствующих в текущем представлении $this->data.
     */
    final public function run()
    {
        if (!$this->template_file || !file_exists($this->template_file)) {
            throw new \RuntimeException(
                __METHOD__ . ': Не найден или явно не укзан шаблон вида ' . $this->template_file
            );
        }

        // Если в шаблоне будет вызван код, генерирующий исключения (например, в методе $this->getRealTemplatePath()),
        // то отлавливаем и бросаем его дальше, в Krugozor\Framework\Application::run().
        try {
            ob_start();
            require $this->template_file;
            $this->output = ob_get_clean();
        } catch (\Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * @return string
     */
    public function getOutput($trim = true): string
    {
        if ($trim) {
            $this->output = Helper\Format::cleanWhitespace($this->output);
        }

        return $this->output;
    }

    /**
     * @param Notification $notification
     * @return View
     */
    final public function setNotification(Notification $notification): self
    {
        $this->notification = $notification;

        return $this;
    }

    /**
     * @return Notification
     */
    final public function getNotification(): ?Notification
    {
        return $this->notification;
    }

    /**
     * Возвращает путь до CSS-файла.
     *
     * @param string|null $module
     * @param string|null $path
     * @return string
     */
    final public function getCss(?string $module = null, ?string $path = null): string
    {
        if (!$module && !$path) {
            $full_path = $this->getPageId('/') . '.css';
        } else {
            $full_path = "$module/$path";
        }

        return '<link rel="stylesheet" href="/css/' . $full_path . '" type="text/css" />' . PHP_EOL;
    }

    /**
     * Возвращает путь до JS-файла.
     *
     * @param string|null $module
     * @param string|null $path
     * @return string
     */
    final public function getJs(?string $module = null, ?string $path = null): string
    {
        if (!$module && !$path) {
            $full_path = $this->getPageId('/') . '.js';
        } else {
            $full_path = "$module/$path";
        }

        return '<script src="/js/' . $full_path . '"></script>' . PHP_EOL;
    }

    /**
     * Возвращает строковой ID в виде строки `модуль_контроллер`, например: `advert_frontend-category-list` где
     * advert - имя модуля \Krugozor\Framework\Module\Advert,
     * frontend-category-list - имя контроллера \Krugozor\Framework\Module\Advert\Controller\FrontendCategoryList
     *
     * @param string $separator разграничитель между значениями модуля и контроллера
     * @return string
     */
    final public function getPageId(string $separator = '_'): string
    {
        return
            $this->getRequest()->getModuleName()->getUriStyle() .
            $separator .
            $this->getRequest()->getControllerName()->getUriStyle();
    }

    /**
     * Устанавливает файл шаблона.
     * Метод применяется в случаях, когда необходимо переустановить автоматически определенный файл шаблона.
     *
     * @param string $template_file
     * @return View
     */
    final public function setTemplateFile(string $template_file): self
    {
        $this->template_file = $template_file;

        return $this;
    }

    /**
     * Устанавливает значение для $this->enabled_debug_info.
     *
     * @param bool $value
     * @return View
     */
    final public function setDebugInfoFlag(bool $value = false): self
    {
        $this->enabled_debug_info = $value;

        return $this;
    }

    /**
     * Метод принимает строку $path вида 'ИмяМодуля/ИмяШаблона' и возвращает
     * "реальный" (физический) путь к шаблону.
     *
     * @param string $path абстрактный путь до файла шаблона
     * @return string физический путь к файлу шаблона
     */
    final protected function getRealTemplatePath(string $path): string
    {
        list($module, $file) = explode('/', $path);

        if (!$module) {
            throw new \RuntimeException(
                __METHOD__ . ": Не указан модуль при подключении второстепенного шаблона ($path)"
            );
        }

        if (!$file) {
            throw new \RuntimeException(
                __METHOD__ . ": Не указан файл при подключении второстепенного шаблона ($path)"
            );
        }

        $path = implode(DIRECTORY_SEPARATOR, [Application::getAnchor($module)::getPath(), 'Template', $file]) . '.phtml';
        if (!file_exists($path)) {
            throw new \RuntimeException(
                __METHOD__ . ": Не найден подключаемый файл второстепенного шаблона ($path)"
            );
        }

        return $path;
    }
}