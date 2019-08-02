<?php

namespace Krugozor\Framework\Helper;

use Krugozor\Framework\Statical\Strings;

/**
 * Класс-хэлпер для форматирования данных, выводимых в шаблоне.
 */
class Format
{
    private static $instance;

    /**
     * Предопределённый массив методов и их последовательностей в выполнении.
     *
     * Если в метод run() передаётся только один аргумент - строка для форматирования,
     * то строка обрабатывается последовательно методами, упомянутыми в этом массиве.
     * Если в метод run(), кроме строки, передаются ещё и имена
     * определённых методов данного класса или стандартных функций PHP, которыми нужно обработать строку,
     * то они так же вызываются согласно последовательности,
     * определённой в массиве, вне зависимости
     * от того, в какой последовательности они определены в вызове метода run().
     *
     * @var array
     */
    private static $default_methods = array('trim', 'decode', 'hsc', 'nl2br');

    /**
     * @return Format
     */
    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Основной метод класса, умеющий обрабатывать переменную несколькими методами подряд.
     * Принимает переменную (строку или массив), которую необходимо обработать
     * и от 0 до N параметров - имена методов или функций, которыми нужно обработать переменную.
     *
     * Пример использования:
     *
     *    $var = $myDB->run($var, "hsc");              - применяет только метод hsc к переменной var.
     *    $var = $myDB->run($var, "hsc", "decode");    - применяет к переменной var методы hsc и decode.
     *    $var = $myDB->run($var);                     - применяет к переменной var все основные методы
     *                                                   перечисленные в массиве self::$default_methods.
     *    $var = $myDB->run($var, "nl2br");            - применяет *стандартную* функцию PHP nl2br
     *                                                   к переменной var.
     * @param mixed обрабатываемая переменная в виде строки или массива и имена методов форматирования
     * @return mixed
     */
    public function run()
    {
        $num_args = func_num_args();

        // Если кол-во аргуметов, переданных в функцию, равно 1,
        // значит, присоеденяем к массиву self::$default_methods, в начало,
        // значение переменной, переданной этой функции.
        if (1 === $num_args) {
            $arg_list = self::$default_methods;
            $temp = func_get_args();
            array_unshift($arg_list, $temp[0]);
            $num_args = count($arg_list);
        } else {
            $arg_list = func_get_args();
        }

        // Значение, которое необходимо обработать.
        $in = array_shift($arg_list);

        // Возможен такой вариант, когда метод format вызовут с нелогичной последовательностью тех аргументов,
        // которые являются определёнными в строгой последовательности выполнения в массиве self::$default_methods.
        // Например, так: $out->run($string, 'hsc', 'decode') - здесь, по сути,
        // нет никакого смысла выполнять decode() после hsc().
        // Для этого и существует нижестоящий код: он сортирует список аргументов в том порядке,
        // в котором они определены в массиве self::$default_methods.
        // Теперь, если метод будет вызван с последовательностью ('hsc', 'decode'),
        // то код его отсортирует в нормальном порядке, т.е. так: ('decode', 'hsc', ...).
        // Методы, не перечисленные в self::$default_methods останутся стоять в своей
        // позиции.

        foreach (self::$default_methods as $key => $method) {
            if (($index = array_search($method, $arg_list)) !== false && $index !== $key) {
                for ($j = 0; $j < count($arg_list); $j++) {
                    if (in_array($arg_list[$j], self::$default_methods)) {
                        $temp = $arg_list[$j];
                        $arg_list[$j] = $arg_list[$index];
                        $arg_list[$index] = $temp;
                    }
                }
            }
        }

        for ($i = 0; $i < $num_args - 1; $i++) {
            $in = $this->processTransformation($in, $arg_list[$i]);
        }

        return $in;
    }

    /**
     * Обработчик данных, которые были получены от пользовательского ввода.
     * Вырезает html-теги, после чего применяет функцию $this->run()
     * с параметрами по умолчанию.
     *
     * @param mixed
     * @return mixed
     */
    public function userDataOutput($value)
    {
        return $this->run(strip_tags($value));
    }

    /**
     * Метод возвращает результат работы функции html_entity_decode
     * с параметром $mode (по умолчанию - ENT_QUOTES - преобразуются и двойные, и одиночные кавычки).
     */
    public static function decode($string, $mode = ENT_QUOTES)
    {
        return html_entity_decode($string, $mode, 'UTF-8');
    }

    /**
     * Метод возвращает результат работы функции htmlspecialchars
     * с параметром $mode (по умолчанию - ENT_QUOTES - преобразуются и двойные, и одиночные кавычки).
     */
    public static function hsc($in, $mode = ENT_QUOTES)
    {
        return htmlspecialchars($in, $mode, 'UTF-8');
    }

    /**
     * Заменяет символы новой строки идущие подряд
     * на один символ новой строки, удаляет табуляцию и символ \r.
     * Метод применяется для форматирования конечного HTML.
     *
     * @param string
     * @return string
     * @static
     */
    public static function cleanWhitespace($in)
    {
        $in = preg_replace("/(\r?\n)+/", '', $in);
        $in = preg_replace("/(\t)+/", '', $in);
        $in = preg_replace("/ +/", ' ', $in);
        $in = preg_replace("/> </", '><', $in);

        return $in;
    }

    /**
     * Расстановка пробелов после знаков пунктуации.
     *
     * @param string $str
     * @return string
     */
    public static function spaceAfterPunctuation($str)
    {
        return preg_replace('~(\S)([.,:;?!])(\S)~', '$1$2 $3', $str);
    }

    /**
     * Формирование значений для JavaScript-переменных, которые попадают, напрмиер, в alert() или confirm().
     *
     * @param string строка, возможно с параметрами как у Strings::createMessageFromParams
     * @param array $params массив с параметрами как у Strings::createMessageFromParams
     * @return string
     */
    public static function js($str, $params = array())
    {
        $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
        $str = htmlspecialchars($str, ENT_QUOTES, 'UTF-8');

        if ($params) {
            array_walk($params, function (&$item, $key) {
                $item = self::js($item);
            });

            $str = Strings::createMessageFromParams($str, $params, false);
        }

        return $str;
    }

    /**
     * Форматирует количество байтов в человекопонятные единицы измерения информации.
     *
     * @param int количество байтов
     * @param string
     */
    public static function createPhpFormatBytes($val)
    {
        $store = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');

        for ($i = 0, $j = 1024; $val >= $j; $val = $val / $j, $i++) ;

        return sprintf('%.1f', round($val, 2)) . (isset($store[$i]) ? $store[$i] : '');
    }

    /**
     * Функция "красиво" обрезает строку $str до максимум $num символов,
     * если она больше числа $num и добавляет строку $postfix
     * в конец строки. Обрезание строки идет после последнего символа
     * $char в строке.
     *
     * @param string $str обрабатываемая строка
     * @param int $num максимальное количество символов
     * @param string $postfix строка, дописываемая к обрезанной строке
     * @return string
     * @static
     */
    public static function getPreviewStr($str, $num = 300, $postfix = '…', $char = ' ')
    {
        if (mb_strlen($str) > $num) {
            $str = mb_substr($str, 0, $num);
            $str = mb_substr($str, 0, mb_strrpos($str, $char));
            $str .= $postfix;
        }

        return $str;
    }

    /**
     * Склонение существительных с числительными.
     * Функция принимает число $n и три строки -
     * разные формы произношения измерения величины.
     * Необходимая величина будет возвращена.
     * Например: triumviratForm(100, "рубль", "рубля", "рублей")
     * вернёт "рублей".
     *
     * @param int величина
     * @param array|CoverArray
     * @return string
     * @static
     */
    public static function triumviratForm($value, $triumvirat_forms)
    {
        $value = abs($value) % 100;
        $value1 = $value % 10;

        if ($value > 10 && $value < 20) {
            return $triumvirat_forms[2];
        } else if ($value1 > 1 && $value1 < 5) {
            return $triumvirat_forms[1];
        } else if ($value1 == 1) {
            return $triumvirat_forms[0];
        }

        return $triumvirat_forms[2];
    }

    /**
     * Обрабатывает переменную $in функцией $fun.
     * Переменная $in может быть многомерным массивом
     * любого уровня вложенности или строкой.
     *
     * @param mixed переменная или массив
     * @param string имя функции
     * @return mixed
     */
    protected function processTransformation($in, $method)
    {
        if (!is_array($in)) {
            if (!function_exists($method) && method_exists($this, $method)) {
                $in = call_user_func(array($this, $method), $in);
            } else if (function_exists($method)) {
                $in = $method($in);
            } else {
                trigger_error('Метод ' . $method . ' не является методом класса ' .
                    __CLASS__ . ' и не является функцией.', E_USER_WARNING);
            }
        } else {
            foreach ($in as $k => $v) {
                $in[$k] = $this->processTransformation($v, $method);
            }
        }

        return $in;
    }

    private function __construct()
    {
    }
}