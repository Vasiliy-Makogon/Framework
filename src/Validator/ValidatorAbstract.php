<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Mapper;

/**
 * Абстрактный класс конкретного валидатора.
 *
 * Валидатор модели посредством метода validate() возвращает false в случае,
 * если имеется факт ошибки и true в обратном случае.
 */
abstract class ValidatorAbstract
{
    /**
     * Проверяемое значение.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Ключ ошибки, описанный в файлах i18n конкретного модуля,
     * и установленный в базовый валидатор через Validator::__construct()
     *
     * @var string
     */
    protected $error_key;

    /**
     * Массив вида ключ => зачение, где ключ - заполнитель строки описания ошибки $this->error_key
     * См. пример валидатор HasBadEmail
     *
     * @var array
     */
    protected $error_params = array();

    /**
     * Булев указатель, говорящий обрывать ли проверку значения, если текущий валидатор обнаружил ошибку.
     *
     * @var boolean
     */
    protected $break = true;

    /**
     * @var Mapper
     */
    protected $mapper;

    /**
     * @param mixed $value проверяемое значение
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Производит валидацию значения.
     * Возвращает false в случае обнаружения ошибки, true в обратном случае.
     *
     * @return bool
     */
    abstract public function validate(): bool;

    /**
     * Возвращает ошибку текущего валидатора.
     *
     * @return array
     */
    public final function getError(): array
    {
        return array($this->error_key, $this->error_params);
    }

    /**
     * @return bool
     */
    public final function getBreak(): bool
    {
        return $this->break;
    }

    /**
     * @param bool $in
     * @return ValidatorAbstract
     */
    public final function setBreak(bool $in): self
    {
        $this->break = $in;

        return $this;
    }
}