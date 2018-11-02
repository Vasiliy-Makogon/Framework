<?php

namespace Krugozor\Framework\Helper;

use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Html\ElementInput;
use Krugozor\Framework\Html\ElementLabel;
use Krugozor\Framework\Html\ElementOptgroup;
use Krugozor\Framework\Html\ElementOption;
use Krugozor\Framework\Html\ElementSelect;
use Krugozor\Framework\Html\ElementTextarea;
use Krugozor\Framework\Statical\Strings;

/**
 * Класс-хэлпер для генерации элементов форм
 * и полей, выводящих ошибки валидации.
 */
class Form
{
    /**
     * @var Form
     */
    private static $instance;

    /**
     * Загруженный шаблон вывода ошибки.
     *
     * @var string
     */
    private $error_template;

    /**
     * @return Form
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Устанавливает шаблон для HTML-кода вывода ошибок.
     *
     * @param string $template путь к шаблону
     */
    public function setFieldErrorTemplate($template)
    {
        if (!file_exists($template)) {
            throw new \RuntimeException('Не найден шаблон вывода ошибок указанный по адресу: ' . $template);
        }

        $this->error_template = file_get_contents($template);
    }

    /*********************************************************************************
     *   Генераторы полей форм.
     **********************************************************************************/

    /**
     * Возвращает объект ElementInput типа checkbox.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param string|int $checked_value значение сравнения - если $value и $checked_value равны,
     *                                  то то checkbox is checked
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputCheckbox($name, $value, $checked_value = null, $params = array())
    {
        $object = new ElementInput('checkbox');
        $object->name = $name;
        $object->value = $value;
        $object->setCheckedValue($checked_value);
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа radio.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param string|int $checked_value значение сравнения - если $value и $checked_value равны,
     * то radio is checked
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputRadio($name, $value, $checked_value = null, $params = array())
    {
        $object = new ElementInput('radio');
        $object->name = $name;
        $object->value = $value;
        $object->setCheckedValue($checked_value);
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает два html-элемента: hidden поле и checkbox.
     * Обобщённый метод получения двух взаимосвязанных элементов управления.
     *
     * @param string $name имя элемента hidden и checkbox
     * @param string|int $value значение checkbox
     * @param string|int $hidden_value значение hidden
     * @param string|int $checked_value значение сравнения - если $value и $checked_value равны,
     * то checkbox is checked.
     * @param array дополнительные необязательные параметры
     * @return string
     */
    public static function inputFullCheckbox($name, $value, $hidden_value = null, $checked_value = null, $params = array())
    {
        $checkbox = self::inputCheckbox($name, $value, $checked_value, $params);
        $hidden = self::inputHidden($name, $hidden_value);

        return $hidden->getHtml() . $checkbox->gethtml();
    }

    /**
     * Возвращает объект ElementInput типа text.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputText($name, $value, $params = array())
    {
        $object = new ElementInput('text');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа email.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputEmail($name, $value, $params = array())
    {
        $object = new ElementInput('email');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа url.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputUrl($name, $value, $params = array())
    {
        $object = new ElementInput('url');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementTextarea.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementTextarea
     */
    public static function inputTextarea($name, $text, $params = array())
    {
        $object = new ElementTextarea();
        $object->name = $name;
        $object->setText($text);
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа password.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputPassword($name, $value, $params = array())
    {
        $object = new ElementInput('password');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа hidden.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputHidden($name, $value, $params = array())
    {
        $object = new ElementInput('hidden');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа submit.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputSubmit($name, $value, $params = array())
    {
        $object = new ElementInput('submit');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа button.
     *
     * @param string $name имя элемента
     * @param string|int $value значение
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputButton($name, $value, $params = array())
    {
        $object = new ElementInput('button');
        $object->name = $name;
        $object->value = $value;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementInput типа file.
     *
     * @param string $name имя элемента
     * @param array дополнительные необязательные параметры
     * @return ElementInput
     */
    public static function inputFile($name, $params = array())
    {
        $object = new ElementInput('file');
        $object->name = $name;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementLabel.
     *
     * @param string $text текст метки
     * @param string $for ссылка на ID
     * @param array дополнительные необязательные параметры
     * @return ElementLabel
     */
    public static function label($text, $for, $params = array())
    {
        $object = new ElementLabel();
        $object->for = $for;
        $object->setText($text);
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementOption.
     *
     * @param string $value значение value тега option
     * @param string $text текстовой узел-значение тега option
     * @param array дополнительные необязательные параметры
     * @return ElementOption
     */
    public static function inputOption($value, $text = null, $params = array())
    {
        $object = new ElementOption();
        $object->value = $value;
        $object->setText($text);
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementOptgroup.
     *
     * @param string $label значение свойства label
     * @param array дополнительные необязательные параметры
     * @return ElementOptgroup
     */
    public static function inputOptgroup($label = null, $params = array())
    {
        $object = new ElementOptgroup();
        $object->label = $label;
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementSelect.
     *
     * @param string $name имя элемента
     * @param string|int $checked_value значение сравнения - если $value и $checked_value равны,
     * то checkbox is checked.
     * @param array дополнительные необязательные параметры
     * @return ElementSelect
     */
    public static function inputSelect($name, $checked_value = null, $params = array())
    {
        $object = new ElementSelect();
        $object->name = $name;
        $object->setCheckedValue($checked_value);
        $object->setData($params);

        return $object;
    }

    /**
     * Возвращает объект ElementSelect наполненный options
     * значения которого идут в цифровом диапазоне $int_start - $int_stop.
     *
     * @param string $name имя элемента
     * @param int $int_start начальное значение
     * @param int $int_stop конечное значение
     * @param string|int $checked_value значение сравнения - если $value и $checked_value равны,
     * то checkbox is checked.
     * @param array дополнительные необязательные параметры
     * @return ElementSelect
     */
    public static function inputSelectIntegerValues($name, $int_start, $int_stop, $checked_value = null, $params = array())
    {
        $int_start = (int)$int_start;
        $int_stop = (int)$int_stop;

        $object = new ElementSelect();
        $object->name = $name;
        $object->setCheckedValue($checked_value);
        $object->setData($params);

        $option = new ElementOption();
        $option->value = 0;
        $option->setText('');
        $object->addOption($option);

        if ($int_start < $int_stop) {
            for (; $int_start <= $int_stop; $int_start++) {
                $option = new ElementOption();
                $option->value = $int_start;
                $option->setText($int_start);

                $object->addOption($option);
            }
        } else {
            for (; $int_start >= $int_stop; $int_start--) {
                $option = new ElementOption();
                $option->value = $int_start;
                $option->setText($int_start);

                $object->addOption($option);
            }
        }

        return $object;
    }

    /**
     * Возвращает объект ElementSelect наполненный options
     * значения которого идут в цифровом диапазоне, определяемом количеством лет со $start и до $stop.
     * Если цифровые значения явно не указаны, то возвращается select с верхней точкой
     * лет равной now-15 и крайней точкой временного отсчёта равной now-80.
     *
     * @param string $name имя элемента
     * @param string|int $checked_value значение сравнения - если $value и $checked_value равны,
     * то checkbox is checked.
     * @param int $start начальное значение
     * @param int $stop конечное значение
     * @param array дополнительные необязательные параметры
     * @return ElementSelect
     */
    public static function inputSelectYears($name, $checked_value, $params = array(), $start = 15, $end = 80)
    {
        $start = date('Y', time() - 60 * 60 * 24 * 360 * $start);
        $end = date('Y', time() - 60 * 60 * 24 * 360 * $end);

        $object = new ElementSelect();
        $object->name = $name;
        $object->setCheckedValue($checked_value);
        $object->setData($params);

        $option = new ElementOption();
        $option->value = 0;
        $option->setText('');
        $object->addOption($option);

        while ($start >= $end) {
            $option = new ElementOption();
            $option->value = $start;
            $option->setText($start);

            $object->addOption($option);
            $start--;
        }

        return $object;
    }

    /*********************************************************************************
     *   Генератор ошибок полей форм.
     **********************************************************************************/

    /**
     * Принимает CoverArray содержащий одну или более ошибок конкретного поля,
     * возникших в результате валидации полей форм и возвращает
     * строку ошибки в виде HTML-кода.
     * HTML-код берётся из шаблона $this->error_template.
     *
     * @param CoverArray
     * @return string
     */
    public function getFieldError(CoverArray $data = null)
    {
        if (!$data instanceof CoverArray || !$data->count()) {
            return '';
        }

        return Strings::createMessageFromParams(
            $this->error_template,
            ['error_message' => implode('', $data->getDataAsArray())],
            false
        );
    }

    private function __construct()
    {
    }
}