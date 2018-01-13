<?php
use Krugozor\Framework\Registry;

declare(strict_types=1);
define('TIME_START', microtime(true));
error_reporting(E_ALL | E_STRICT);

/**
 * DOCUMENT ROOT проекта.
 *
 * @var string
 */
define('DOCUMENTROOT_PATH', dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'src');

/**
 * Путь к файлу URL-маршрутов.
 *
 * @var string
 */
define('ROUTES_PATH', DOCUMENTROOT_PATH . '/configuration/routes.php');

try {
    Registry::getInstance(
        DOCUMENTROOT_PATH . DIRECTORY_SEPARATOR .
        'configuration' . DIRECTORY_SEPARATOR .
        'config.ini'
    );
} catch (Exception $e) {
    echo $e->getMessage();
    exit;
}

ini_set('display_errors', Registry::getInstance()->DEBUG['DISPLAY_ERRORS']);
ini_set('display_startup_errors', Registry::getInstance()->DEBUG['DISPLAY_STARTUP_ERRORS']);

// Это есть в htaccess
// mb_language('Russian');
// mb_internal_encoding('UTF-8');

// ls /usr/share/locale | grep 'ru'
// locale -a -m
setlocale(LC_ALL, Registry::getInstance()->LOCALIZATION['LOCALES']);
date_default_timezone_set(Registry::getInstance()->LOCALIZATION['TIMEZONE']);

// Обновляем пути на актуальные, с учетом текущего DOCUMENTROOT_PATH
foreach (Registry::getInstance()->PATH as $index => $path) {
    Registry::getInstance()->PATH[$index] = DOCUMENTROOT_PATH . DIRECTORY_SEPARATOR . $path;
}

ini_set('error_log', Registry::getInstance()->PATH['PHP_ERROR_LOG']);


// Спасибо товарищу Сталину за наше счастливое детство

/**
 * Возвращает копию str, в которой все вхождения каждого символа
 * из from были заменены на соответствующий символ в параметре to.
 *
 * @param string $str
 * @param string $from
 * @param string $to
 * @return string
 */
if (!function_exists("mb_strtr")) {
    function mb_strtr($str, $from, $to)
    {
        return str_replace(mb_str_split($from), mb_str_split($to), $str);
    }
}

/**
 * Преобразует строку в массив
 *
 * @param string $str исходная строка
 * @param int $l максимальная длина фрагмента
 * @return array
 */
if (!function_exists("mb_str_split")) {
    function mb_str_split($str, $l = 0)
    {
        if ($l > 0) {
            $ret = array();
            $len = mb_strlen($str, "UTF-8");

            for ($i = 0; $i < $len; $i += $l) {
                $ret[] = mb_substr($str, $i, $l);
            }

            return $ret;
        }

        return preg_split("//u", $str, -1, PREG_SPLIT_NO_EMPTY);
    }
}

/**
 * @see ucfirst
 * @param string $str
 * @return string
 */
if (!function_exists('mb_ucfirst')) {
    function mb_ucfirst($str)
    {
        return mb_strtoupper(mb_substr($str, 0, 1)) . mb_substr($str, 1);
    }
}