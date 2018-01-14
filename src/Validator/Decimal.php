<?php

/**
 * Проверка значения на целое число (без плавающей точки).
 */
class Krugozor_Validator_Decimal extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_UNSIGNED_DECIMAL';

    /**
     * Должно ли проверяемое значение быть знаковым числом.
     * Если значение в true, то число проверяется как знаковое (т.е. может иметь знак),
     * иначе - как беззнаковое.
     *
     * @var boolean
     */
    private $signed = false;

    /**
     * @param boolean $signed
     * @return Krugozor_Validator_Decimal
     */
    public function setSigned($signed)
    {
        $this->signed = (bool)$signed;

        return $this;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        if (Krugozor_Static_String::isEmpty($this->value)) {
            // Если передана пустая строка, возвращается true, как будто ошибки нет.
            // Это поведение создано для того, что бы каждый валидатор отвечал лишь за одну проверку.
            // В данном случае, если бы валидатор ругнулся на null или пустую строку,
            // то в модель попала бы информация о некорректном значении, т.е. запись в базу была бы невозможна.
            // Однако, пустая строка, false или null (все эти значения записываются как NULL в СУБД)
            // вполне может ожидаться СУБД.
            // Поэтому, если вместо числа пришла пустая строка, значит это может быть просто NULL, т.е. значения нет.
            return true;
        }

        return Krugozor_Static_Numeric::is_decimal($this->value, $this->signed);
    }
}