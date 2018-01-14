<?php

final class Krugozor_Validator
{
    /**
     * Массив валидаторов.
     *
     * @var array
     */
    private $validators = array();

    /**
     * Массив описания ошибок из текстовых файлов.
     *
     * @var array
     */
    private $error_messages = array();

    /**
     * Ошибки, возвращённые валидаторами.
     *
     * @var array
     */
    private $errors;

    /**
     * Принимает неограниченное количество параметров - строк которые
     * являются путями, указывающими на php-файлы описания ошибок валидаций.
     * Каждая строка имеет вид `ModuleName/fileName`, где
     * ModuleName - имя модуля
     * fileName   - имя php-файла описания ошибок валидаций
     *
     * @param string
     */
    public function __construct()
    {
        $args = func_get_args();
        $error_message_files = array();

        foreach ($args as $arg) {
            list($module, $file) = explode('/', $arg);

            $path = implode(DIRECTORY_SEPARATOR,
                    array(dirname(__DIR__), 'Krugozor', 'Module', ucfirst($module), 'i18n',
                        Krugozor_Registry::getInstance()->LOCALIZATION['LANG'], 'validator', strtolower($file)
                    )
                ) . '.php';

            if (!file_exists($path)) {
                trigger_error("Не найден указанный языковой файл валидатора $file для модуля $module по адресу $path", E_USER_WARNING);
            } else {
                $messages = (array)include $path;

                if ($messages) {
                    $this->error_messages = array_merge_recursive($this->error_messages, $messages);
                }
            }
        }
    }

    /**
     * Добавляет валидатор $validator под ключом $key в коллецию валидаторов.
     *
     * @param string $key ключ, соответствующий имени проверяемого свойства
     * @param Krugozor_Validator_Abstract $validator конкретный валидатор
     * @return Krugozor_Validator
     */
    public final function add($key, Krugozor_Validator_Abstract $validator)
    {
        if (!isset($this->validators[$key])) {
            $this->validators[$key] = array();
        }

        $this->validators[$key][] = $validator;

        return $this;
    }

    /**
     * Проходит по всем валидаторам, добавленным в данный класс,
     * поочерёдно производя валидацию каждого из них.
     * Если валидатор не проходит валидацию, т.е. есть ошибки,
     * метод помещает в массив $this->errors пару ключ => значение,
     * где ключ - ключ, соответствующий имени проверяемого свойства,
     * а значение - масив информации об ошибке.
     *
     * @param void
     * @return Krugozor_Validator
     */
    public final function validate()
    {
        foreach ($this->validators as $key => $validators) {
            foreach ($validators as $validator) {
                if (!$validator->validate()) {
                    $this->errors[$key][] = $validator->getError();

                    if ($validator->getBreak()) {
                        break;
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Добавляет ошибку, иммитируя добавленную ошибку валидатора.
     *
     * @param string $user_key ключ возвращаемого значения
     * @param string $error_key ключ ошибки из файлов описания ошибок
     * @param array $placeholders ассоциативный массив аргументов-заполнителей
     * @return Krugozor_Validator
     */
    public final function addError($user_key, $error_key, $placeholders = array())
    {
        $this->errors[$user_key][] = array($error_key, $placeholders);

        return $this;
    }

    /**
     * Добавляет ошибки, возвращенные моделью.
     *
     * @param array $errors
     * @return Krugozor_Validator
     */
    public final function addModelErrors(array $errors = array())
    {
        foreach ($errors as $key => $data) {
            foreach ($data as $params) {
                $this->addError($key, $params[0], $params[1]);
            }
        }

        return $this;
    }

    /**
     * Возвращает конечный массив с человекопонятными сообщениями об ошибках.
     *
     * @param void
     * @return array
     */
    public final function getErrors()
    {
        $output = array();

        if ($this->errors) {
            foreach ($this->errors as $key => $errors) {
                $output[$key] = array();

                foreach ($errors as $id => $params) {
                    if (empty($this->error_messages[$params[0]])) {
                        trigger_error("Не найдено описание ключа ошибки $params[0]", E_USER_WARNING);

                        $output[$key][$id] = $params[0];
                    } else {
                        $output[$key][$id] = Krugozor_Static_String::createMessageFromParams(
                            $this->error_messages[$params[0]], $params[1]
                        );
                    }
                }
            }
        }

        return $output;
    }
}