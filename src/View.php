<?php

class Krugozor_View
{
    /**
     * Хранилище данных, передаваемых контроллером
     * через магические методы __set и __get.
     *
     * @var Krugozor_Cover_Array
     */
    protected $data;

    /**
     * Хранилище данных файлов интернационализации.
     *
     * @var Krugozor_View_Lang
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
    protected $out;

    /**
     * Массив объектов-хелперов, работающих с view.
     *
     * @var array
     */
    protected $helpers = array();

    /**
     * Разрешено ли выводить отладочную информацию внизу страницы.
     *
     * @var bool
     */
    private $enabled_debug_info = false;

    /**
     * @param null|string путь до файла шаблона
     */
    public function __construct($template_file = null)
    {
        $this->template_file = $template_file;
        $this->data = new Krugozor_Cover_Array();
        $this->lang = new Krugozor_View_Lang();
    }

    /**
     * Возвращает объект запроса.
     * Это лишь краткая форма записи получения данных запроса из View.
     * Данный метод необъодим, т.к. зачастую в шаблонах необходимо иметь
     * данные о параметрах запроса, URL адресе и т.п.
     *
     * @param void
     * @return Krugozor_Http_Request
     */
    final public function getRequest()
    {
        return Krugozor_Context::getInstance()->getRequest();
    }

    /**
     * Возвращает элемент из хранилища данных $this->data.
     *
     * @param void
     * @return mixed
     */
    final public function __get($key)
    {
        return $this->data->$key;
    }

    /**
     * Добавляет новый элемент в хранилище данных $this->data.
     *
     * @param $key ключ
     * @param $value значение
     * @return void
     */
    final public function __set($key, $value)
    {
        $this->data->$key = $value;
    }

    /**
     * Возвращает объект данных интернационализации.
     *
     * @param void
     * @return Krugozor_View_Lang
     */
    final public function getLang()
    {
        return $this->lang;
    }

    /**
     * Возвращает объект-хэлпер $helper_name.
     *
     * @param string $helper_name имя класса-хэлпера
     * @return Krugozor_Helper_Abstract
     */
    final public function getHelper()
    {
        if (!func_num_args()) {
            throw new InvalidArgumentException('Попытка вызова метода ' . __METHOD__ . ' без указания класса-помощника');
        }

        $args = func_get_args();

        $helper_name = array_shift($args);

        switch ($helper_name) {
            case 'Krugozor_Helper_Form':
                if (!isset($this->helpers[$helper_name])) {
                    $this->helpers[$helper_name] = Krugozor_Helper_Form::getInstance();
                    $this->helpers[$helper_name]->setFieldErrorTemplate($this->getRealTemplatePath('Common/FieldError'));
                }
                return $this->helpers[$helper_name];

            default:
                if (!class_exists($helper_name)) {
                    throw new InvalidArgumentException(
                        __METHOD__ . ': Попытка вызвать неизвестный класс-помощник в контексте View'
                    );
                } else {
                    if (!isset($this->helpers[$helper_name])) {
                        $cls = new ReflectionClass($helper_name);

                        // Если хэлпер Singelton, то сохраняем его в хранилище
                        // иначе - просто инстанцируем, возвращаем и "забываем" о нем.
                        if ($cls->hasMethod('getInstance')) {
                            $method = $cls->getMethod('getInstance');

                            if ($method->isStatic()) {
                                $this->helpers[$helper_name] = call_user_func_array(array($cls->getName(), 'getInstance'), $args);
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
     *
     * @param void
     * @return string
     */
    final public function run()
    {
        if (!$this->template_file || !file_exists($this->template_file)) {
            throw new RuntimeException(__METHOD__ . ': Не найден или явно не укзан шаблон вида ' . $this->template_file);
        }

        // Если в шаблоне будет вызван код, генерирующий исключения (например, в методе $this->getRealTemplatePath()),
        // то отлавливаем и бросаем его дальше, в Krugozor_Application->run().
        try {
            ob_start();
            require($this->template_file);
            $this->out = ob_get_clean();
        } catch (Exception $e) {
            ob_end_clean();
            throw $e;
        }
    }

    /**
     * Возвращает сгенерированный html-код.
     *
     * @param void
     * @return string
     */
    final public function getOutput()
    {
        return Krugozor_Helper_Format::cleanWhitespace($this->out);
    }

    /**
     * @param Krugozor_Notification
     * @return void
     */
    final public function setNotification(Krugozor_Notification $notification)
    {
        $this->data['notification'] = $notification;
    }

    /**
     * Возвращает путь до JS-файла текущего контроллера.
     * Если передан параметр $last_mofied, то в query string подставляется метка - дата последнего изменения файла.
     *
     * @param bool
     * @return string
     */
    final public function getPathToJsModule($last_mofied = false)
    {
        $file = '/http/js/modules/' . $this->getPageId('/') . '.js';
        $qs = $last_mofied ? '?' . filemtime(DOCUMENTROOT_PATH . $file) : '';

        return '<script type="text/javascript" src="' . $file . $qs . '"></script>' . PHP_EOL;
    }

    /**
     * Возвращает путь до CSS-файла текущего контроллера.
     *
     * @param bool Если передан параметр $last_mofied, то в query string подставляется метка - дата последнего изменения файла.
     * @param bool frontend - true, backend - false
     * @return string
     */
    final public function getPathToCssModule($last_mofied = false)
    {
        $file = '/http/css/modules/' . $this->getPageId('/') . '.css';
        $qs = $last_mofied ? '?' . filemtime(DOCUMENTROOT_PATH . $file) : '';

        return '<link rel="stylesheet" href="' . $file . $qs . '" type="text/css" />' . PHP_EOL;
    }

    /**
     * Возвращает путь до JS-файла.
     * Если передан параметр $last_mofied, то в query string подставляется метка - дата последнего изменения файла.
     *
     * @param string
     * @param bool
     * @return string
     */
    final public function getJs($file, $last_mofied = false)
    {
        $qs = $last_mofied ? '?' . filemtime(DOCUMENTROOT_PATH . $file) : '';

        return '<script type="text/javascript" src="' . $file . $qs . '"></script>' . PHP_EOL;
    }

    /**
     * Возвращает путь до CSS-файла.
     * Если передан параметр $last_mofied, то в query string подставляется метка - дата последнего изменения файла.
     *
     * @param string
     * @param bool
     * @return string
     */
    final public function getCss($file, $last_mofied = false)
    {
        $qs = $last_mofied ? '?' . filemtime(DOCUMENTROOT_PATH . $file) : '';

        return '<link rel="stylesheet" href="' . $file . $qs . '" type="text/css" />' . PHP_EOL;
    }

    /**
     * Возвращает строковой ID в виде строки `модуль_контроллер`, например: `advert_frontend-category-list` где
     * advert - имя модуля Advert frontend-category-list - имя контроллера Krugozor_Module_Advert_Controller_FrontendCategoryList
     *
     * @param string $separator разграничитель между значениями модуля и контроллера
     * @return string
     */
    final public function getPageId($separator = '_')
    {
        return $this->getRequest()->getModuleName()->getUriStyle() . $separator .
            $this->getRequest()->getControllerName()->getUriStyle();
    }

    /**
     * Устанавливает файл шаблона.
     * Метод применяется в случаях, когда необходимо переустановить автоматически определенный файл шаблона.
     *
     * @param string $template_file
     * @return Krugozor_View
     */
    final public function setTemplateFile($template_file)
    {
        $this->template_file = $template_file;

        return $this;
    }

    /**
     * Устанавливает значение для $this->enabled_debug_info.
     *
     * @param bool $value
     * @return Krugozor_View
     */
    final public function setDebugInfoFlag($value = false)
    {
        $this->enabled_debug_info = (bool)$value;

        return $this;
    }

    /**
     * Метод принимает строку $path вида 'ИмяМодуля/ИмяШаблона' и возвращает
     * "реальный" (физический) путь к шаблону.
     *
     * @param string $path абстрактный путь до файла шаблона
     * @return string физический путь к файлу шаблона
     */
    final protected function getRealTemplatePath($path)
    {
        list($module, $file) = explode('/', $path);

        if (!$module) {
            throw new RuntimeException(__METHOD__ . ": Не указан модуль при подключении второстепенного шаблона ($path)");
        }

        if (!$file) {
            throw new RuntimeException(__METHOD__ . ": Не указан файл при подключении второстепенного шаблона ($path)");
        }

        $path = implode(DIRECTORY_SEPARATOR, array(__DIR__, 'Module', $module, 'Template', $file)) . '.phtml';

        if (!file_exists($path)) {
            throw new RuntimeException(__METHOD__ . ": Не найден подключаемый файл второстепенного шаблона ($path)");
        }

        return $path;
    }
}