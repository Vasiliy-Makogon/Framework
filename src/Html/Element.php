<?php

abstract class Krugozor_Html_Element extends Krugozor_Cover_Abstract_Simple
{
    /**
     * Массив данных вида имя_аттрибута => значение.
     * Данные аттрибуты будут представлены в полученном элементе управления.
     *
     * @access protected
     * @var array
     */
    protected $data = array();

    /**
     * Массив допустимых аттрибутов конкретного элемента управления.
     *
     * @access protected
     * @var array
     */
    protected $attrs = array();

    /**
     * Массив допустимых аттрибутов типа coreattrs
     * и их default значения согласно спецификации.
     *
     * @access protected
     * @var array
     */
    protected $coreattrs = array();

    /**
     * Массив допустимых аттрибутов типа i18n
     * и их default значения согласно спецификации.
     *
     * @access protected
     * @var array
     */
    protected $i18n = array();

    /**
     * Массив допустимых аттрибутов типа events
     * и их default значения согласно спецификации.
     *
     * @access protected
     * @var array
     */
    protected $events = array();

    /**
     * Массив всех допустимых аттрибутов и их default значений
     * согласно спецификации. Массив представляет собой объединений массивов
     * $this->attrs, $this->coreattrs, $this->i18n, $this->events.
     * Объединение происходит в конструкторе конкретного класса.
     *
     * @access protected
     * @var array
     */
    protected $all_attrs = array();

    /**
     * Объект типа DOMDocument.
     *
     * @access protected
     * @var object
     */
    protected $doc;

    /**
     * Массив настроек класса.
     *
     * @access protected
     * @var array
     */
    protected $configs = array();

    /**
     * Конструктор инициализирует все массивы
     * базовых аттрибутов {@link $coreattrs}, {@link $i18n}, {@link $events},
     * а так же устанавливает некоторые настройки класса {@link $configs}
     *
     * @access public
     * @param void
     * @return void
     */
    public function __construct()
    {
        $this->coreattrs = array
        (
            'id' => 'ID',
            'class' => 'NMTOKENS',
            'style' => 'CDATA',
            'title' => 'CDATA'
        );

        $this->i18n = array
        (
            'lang' => 'CDATA',
            'dir' => array('ltr', 'rtl')
        );

        $this->events = array
        (
            'onclick' => 'Script',
            'ondblclick' => 'Script',
            'onmousedown' => 'Script',
            'onmouseup' => 'Script',
            'onmouseover' => 'Script',
            'onmousemove' => 'Script',
            'onmouseout' => 'Script',
            'onkeypress' => 'Script',
            'onkeydown' => 'Script',
            'onkeyup' => 'Script'
        );

        // Строгая проверка на присаивание тегам аттрибутов.
        $this->configs['strict_mode'] = true;
    }

    /**
     * Устанавливает аттрибут $key со значением $value для текущега элемента.
     * Расширение метода __set базового класса Cover_Abstract.
     *
     * @access public
     * @param string $key string имя аттрибута элемента HTML
     * @param string $value string значение аттрибута элемента HTML
     * @return void
     * @todo: Отрефакторить в соответствии с http://www.w3.org/TR/html5/common-microsyntaxes.html#common-microsyntaxes
     *       + переименовать в setAttribute и data переименовать в attribute
     */
    public function __set($key, $value)
    {
        // Если значение аттрибута представленно в виде :name:,
        // то это значит, что значение данного аттрибута должно быть
        // эквивалентно значению аттрибута под именем name, который _должен_
        // быть передан _перед_ ним.
        // Например, код: $object->setData(array('id' => 'myinput', 'name'=>':id:'));
        // даст результат: <input name="myinput" id="myinput" ... />
        if (preg_match('~:([a-z]+):~', $value, $matches)) {
            $this->data[$key] =& $this->data[$matches[1]];

            return;
        }

        if ($this->configs['strict_mode']) {
            // неизвестный аттрибут
            if (!isset($this->all_attrs[$key])) {
                // и не data-...
                if (!preg_match('~^data-([a-z][a-z0-9]+)$~i', $key)) {
                    throw new InvalidArgumentException('Попытка присвоить неизвестный аттрибут ' . $key . ' тегу ' .
                        __CLASS__ . '::' . $this->type);
                }
            }

            if (!empty($this->all_attrs[$key]) && is_array($this->all_attrs[$key])) {
                if (!in_array($value, $this->all_attrs[$key])) {
                    throw new InvalidArgumentException('Попытка присвоить аттрибуту ' . $key . 'недопустимое значение');
                }
            }

            if (!empty($this->all_attrs[$key])) {
                switch ($this->all_attrs[$key]) {
                    case 'Script':
                    case 'ContentTypes':
                    case 'URI':
                    case 'NMTOKENS':
                        break;

                    case 'CDATA':
                    case 'Text':
                        break;

                    case 'Character':
                        if (empty($value) || strlen($value) !== 1 || !preg_match("~^[a-z0-9]$~i", $value)) {
                            throw new InvalidArgumentException('Попытка присвоить недопустимое значение ' . $value .
                                ' аттрибуту ' . $key . ' (ожидается один символ)');
                        }
                        break;

                    case 'ID':
                    case 'IDREF':
                        if ($value === '' || !preg_match("~^[a-z][a-z0-9-_:.]*$~i", $value)) {
                            throw new InvalidArgumentException('Попытка присвоить недопустимое значение ' . $value .
                                ' аттрибуту ' . $key . ' (ожидается некорректный ID)');
                        }
                        break;

                    case 'Number':
                        if (!strlen($value) || preg_match("~^[^0-9]$~", $value)) {
                            throw new InvalidArgumentException('Попытка присвоить недопустимое значение ' . $value .
                                ' аттрибуту ' . $key . ' (ожидается цифра)');
                        }
                        break;
                }
            }
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * В данном методе должны быть реализованы основные действия по формированию
     * объекта $this->doc являющегося экземпляром класса DOMDocument и содержащего
     * нужный элемент управления HTML.
     *
     * @access public
     * @param void
     * @return string
     */
    abstract protected function createDocObject();

    /**
     * Преобразует строку $xml, являющуюся результатом работы метода saveXml()
     * класса DOMDocument в валидный HTML-код, путём устранения декларации XML.
     *
     * @access public
     * @param string
     * @return string
     */
    protected function xml2html($xml)
    {
        return trim(str_replace('<?xml version="1.0" encoding="utf-8"?>', '', $xml));
    }

    /**
     * Возвращает html-код элемента управления.
     *
     * @access public
     * @param void
     * @return string
     */
    public function getHtml()
    {
        $this->createDocObject();
        return $this->xml2html($this->doc->saveXML());
    }

    /**
     * Меняет установки конфигурации.
     *
     * @access public
     * @param string $key имя ключа параметра конфигурации
     * @param mixed value новое значение
     * @return void
     */
    public function configSet($key, $value)
    {
        if (!isset($this->configs[$key])) {
            throw new InvalidArgumentException(
                __CLASS__ . ': Попытка изменить неизвестное свойство массива конфигурации'
            );
        }

        $this->configs[$key] = $value;
    }

    public function exportNode()
    {
        $this->createDocObject();

        return $this->doc->firstChild;
    }

    public function getDocObject()
    {
        $this->createDocObject();

        return $this->doc;
    }

    /**
     *
     *
     * @param $name
     * @param $value
     */
    public function setAttribute($name, $value)
    {
        $this->$name = $value;

        return $this;
    }

    /**
     * Удаляет аттрибут $name.
     *
     * @param $name имя аттрибута
     * @return Krugozor_Html_Element
     */
    public function removeAttribute($name)
    {
        unset($this->data[$name]);

        return $this;
    }
}