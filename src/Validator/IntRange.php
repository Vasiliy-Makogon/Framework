<?php

/**
 * Проверка числового значения на соответствие диапазону.
 */
class Krugozor_Validator_IntRange extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_INT_RANGE';

    /**
     * Минимальная величина диапазона.
     *
     * @var int
     */
    private $min = null;

    /**
     * Максимальная величина диапазона.
     *
     * @var int
     */
    private $max = null;

    const ZERO = 0;

    const PHP_MAX_INT_32 = 2147483647;

    /**
     * @param int $min
     * @return Krugozor_Validator_IntRange
     */
    public function setMin($min)
    {
        $this->min = (int)$min;

        return $this;
    }

    /**
     * @param int $max
     * @return Krugozor_Validator_IntRange
     */
    public function setMax($max)
    {
        $this->max = (int)$max;

        return $this;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            return true;
        }

        if ($this->min !== null && $this->max !== null) {
            if ($this->value < $this->min || $this->value > $this->max) {
                $this->error_params = array('min' => $this->min, 'max' => $this->max);

                return false;
            }

            return true;
        }
    }
}