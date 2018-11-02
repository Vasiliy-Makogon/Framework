<?php

namespace Krugozor\Framework;

use Krugozor\Framework\Statical\Strings;
use Krugozor\Framework\Validator\ValidatorAbstract;

final class Validator
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
     * @param string[] ...$args
     * @throws \RuntimeException
     */
    public function __construct(string ...$args)
    {
        foreach ($args as $arg) {
            list($module, $file) = explode('/', $arg);

            $path = implode(DIRECTORY_SEPARATOR,
                    array(dirname(__DIR__), 'Framework', 'Module', ucfirst($module), 'i18n',
                        Registry::getInstance()->LOCALIZATION['LANG'], 'validator', strtolower($file)
                    )
                ) . '.php';

            if (!file_exists($path)) {
                throw new \RuntimeException(
                    "Не найден указанный языковой файл валидатора $file для модуля $module по адресу $path"
                );
            } else {
                $messages = (array) include_once $path;

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
     * @param ValidatorAbstract $validator конкретный валидатор
     * @return Validator
     */
    public final function add(string $key, ValidatorAbstract $validator): self
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
     * @return Validator
     */
    public final function validate(): self
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
     * @return Validator
     */
    public final function addError(string $user_key, string $error_key, array $placeholders = []): self
    {
        $this->errors[$user_key][] = array($error_key, $placeholders);

        return $this;
    }

    /**
     * Добавляет ошибки, возвращенные моделью.
     *
     * @param array $errors
     * @return Validator
     */
    public final function addModelErrors(array $errors = []): self
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
     * @return array
     */
    public final function getErrors(): array
    {
        $output = [];

        if ($this->errors) {
            foreach ($this->errors as $key => $errors) {
                $output[$key] = array();

                foreach ($errors as $id => $params) {
                    if (empty($this->error_messages[$params[0]])) {
                        trigger_error("Не найдено описание ключа ошибки $params[0]", E_USER_WARNING);

                        $output[$key][$id] = $params[0];
                    } else {
                        $output[$key][$id] = Strings::createMessageFromParams(
                            $this->error_messages[$params[0]], $params[1]
                        );
                    }
                }
            }
        }

        return $output;
    }
}