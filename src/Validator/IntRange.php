<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка числового значения на соответствие диапазону.
 */
class IntRange extends ValidatorAbstract
{
    /**
     * @var string
     */
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
     * @return IntRange
     */
    public function setMin(int $min): self
    {
        $this->min = $min;

        return $this;
    }

    /**
     * @param int $max
     * @return IntRange
     */
    public function setMax(int $max): self
    {
        $this->max = $max;

        return $this;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        if (Strings::isEmpty($this->value)) {
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