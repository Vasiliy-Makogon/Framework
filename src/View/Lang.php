<?php

namespace Krugozor\Framework\View;

use Krugozor\Framework\Context;
use Krugozor\Cover\CoverArray;
use Krugozor\Framework\Html\Title;
use Krugozor\Framework\Registry;

/**
 * Класс интернационализации.
 * Подгрузка файлов интернационализации и merge этих данных.
 */
class Lang extends CoverArray
{
    /**
     * Метод загружает в $this->data данные из языковых файлов,
     * которые в конструкторе должны представлять из себя строки вида
     * имя_модуля/имя_файла_с_языковыми_данными.
     *
     * @param string[] ...$args
     * @return Lang
     */
    public function loadI18n(string ...$args): self
    {
        foreach ($args as $arg) {
            list($module, $file) = explode('/', $arg);

            $lang = Context::getInstance()->getRequest()->getRequest('lang', 'string')
                    ?: Registry::getInstance()->LOCALIZATION['LANG'];

            $anchor = 'Krugozor\\Framework\\Module\\' . ucfirst($module) . '\\Anchor';
            if (!class_exists($anchor)) {
                throw new \RuntimeException("Not found Anchor-file at `$anchor`");
            }

            $path = implode(DIRECTORY_SEPARATOR, [
                    $anchor::getPath(), 'i18n', $lang, 'controller', $file
                ]) . '.php';

            if (!file_exists($path)) {
                throw new \RuntimeException('Не найден файл интернационализации по адресу ' . $path);
            }

            $this->setData($this->arrayMergeRecursiveDistinct($this->getDataAsArray(), (array)include_once($path)));
        }

        return $this;
    }

    /**
     * Добавляет title из файла интернационализации в экземпляр Title
     *
     * @return Lang
     */
    public function addTitle(): self
    {
        Title::getInstance()->add($this->title);

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
     * @param array[] ...$arrays
     * @return array
     */
    protected function arrayMergeRecursiveDistinct(array ...$arrays): array
    {
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