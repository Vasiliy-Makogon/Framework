<?php

/**
 * Абстрактный класс конкретного валидатора.
 *
 * Валидатор посредством метода validate() возвращает false в случае,
 * если имеется факт ошибки и true в обратном случае.
 */
abstract class Krugozor_Validator_Abstract
{
    /**
     * Проверяемое значение.
     *
     * @var mixed
     */
    protected $value;

    /**
     * Ключ ошибки, описанный в файлах i18n, и установленный в базовый валидатор
     * через Krugozor_Validator::__construct().
     *
     * @var string
     */
    protected $error_key;

    /**
     * Массив вида ключ => начение, где ключ - заполнитель строки описания ошибки $this->error_key
     * См. пример валидатор Krugozor_Validator_HasBadEmail
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
     * Mapper для работы с БД.
     *
     * @var Krugozor_Mapper
     */
    protected $mapper;

    /**
     * @param mixed $value
     */
    public function __construct($value)
    {
        $this->value = $value;
    }

    /**
     * Производит валидацию значения.
     * Возвращает false в случае обнаружения ошибки, true в обратном случае.
     *
     * @param void
     * @return bool
     */
    abstract public function validate();

    /**
     * Возвращает ошибку текущего валидатора.
     *
     * @param void
     * @return array
     */
    public final function getError()
    {
        return array($this->error_key, $this->error_params);
    }

    /**
     * @param void
     * @return bool
     */
    public final function getBreak()
    {
        return $this->break;
    }

    /**
     * @param bool
     * @return Krugozor_Validator_Abstract
     */
    public final function setBreak($in)
    {
        $this->break = (bool)$in;

        return $this;
    }
}