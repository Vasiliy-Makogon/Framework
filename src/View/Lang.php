<?php

/**
 * Класс интернационализации.
 * Подгрузка файлов интернационализации и merge этих данных.
 */
class Krugozor_View_Lang extends Krugozor_Cover_Array
{
    /**
     * Метод загружает в $this->data данные из языковых файлов,
     * которые в конструкторе должны представлять из себя строки вида
     * имя_модуля/имя_файла_с_языковыми_данными.
     *
     * @param string $string1 , string $string2, [, string $...]
     * @return Krugozor_View_Lang
     */
    public function loadI18n()
    {
        $args = func_get_args();
        $data = array();

        foreach ($args as $arg) {
            list($module, $file) = explode('/', $arg);

            $lang = Krugozor_Context::getInstance()->getRequest()->getRequest('lang', 'string') ?: Krugozor_Registry::getInstance()->LOCALIZATION['LANG'];
            $lang_path = array(dirname(__DIR__), 'Module', ucfirst($module), 'i18n', $lang, 'controller', $file);
            $path = implode(DIRECTORY_SEPARATOR, $lang_path) . '.php';

            if (!file_exists($path)) {
                throw new Exception('Не найден файл интернационализации по адресу ' . $path);
            }

            $this->setData($this->arrayMergeRecursiveDistinct($this->getDataAsArray(), (array)include_once($path)));
        }

        return $this;
    }

    /**
     * Добавляет title из файла интернационализации в экземпляр Krugozor_Html_Title
     *
     * @return $this
     */
    public function addTitle()
    {
        Krugozor_Html_Title::getInstance()->add($this->title);

        return $this;
    }

    /**
     * Рекурсивно объединяет любое количество массивов-параметров, заменив
     * значения со строковыми ключами значениями из последних массивов.
     * Если следующее присваиваемое значение является массивом, то он
     * автоматически обрабатывает оба аргумента как массив.
     * Числовые записи добавляются, а не заменяются, но только если они
     * уникальны.
     *
     * Пример:
     * print_r(Krugozor_Static_Array::arrayMergeRecursiveDistinct(
     * array('title' => array('Раз'), 'key' => 'value1', 111),
     * array('title' => array('Два'), 'key' => 'value2', 222),
     * array('title' => array('Три'), 'key' => 'value3', 333),
     * array(0 => 111)));
     *
     * @param miixed
     * @return array
     */
    private function arrayMergeRecursiveDistinct()
    {
        $arrays = func_get_args();
        $base = array_shift($arrays);

        if (!is_array($base)) {
            $base = empty($base) ? array() : array($base);
        }

        foreach ($arrays as $append) {
            if (!is_array($append)) {
                $append = array($append);
            }

            foreach ($append as $key => $value) {
                if (!array_key_exists($key, $base) and !is_numeric($key)) {
                    $base[$key] = $value;
                }

                if (is_array($value) or isset($base[$key]) && is_array($base[$key])) {
                    $base[$key] = call_user_func(array($this, __METHOD__), $base[$key], $append[$key]);
                } else if (is_numeric($key)) {
                    if (!in_array($value, $base)) {
                        $base[] = $value;
                    }
                } else {
                    $base[$key] = $value;
                }
            }
        }

        return $base;
    }
}