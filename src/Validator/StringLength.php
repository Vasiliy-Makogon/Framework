<?php

/**
 * Проверка значения (строки) на определенную длинну.
 */
class Krugozor_Validator_StringLength extends Krugozor_Validator_Abstract
{
    protected $error_key = 'INVALID_STRING_LENGTH';

    const MD5_MAX_LENGTH = 32;

    const VARCHAR_MAX_LENGTH = 255;

    const TEXT_MAX_LENGTH = 65535;

    /**
     * Минимальная длинна строки.
     *
     * @var int
     */
    private $start = 0;

    /**
     * Максимальная длинна строки.
     *
     * @var int
     */
    private $stop = self::VARCHAR_MAX_LENGTH;

    /**
     * @param int $start
     * @return Krugozor_Validator_StringLength
     */
    public function setStart($start)
    {
        $this->start = (int)$start;

        return $this;
    }

    /**
     * @param int $stop
     * @return Krugozor_Validator_StringLength
     */
    public function setStop($stop)
    {
        $this->stop = (int)$stop;

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

        $len = mb_strlen($this->value);

        if (!($len >= $this->start && $len <= $this->stop)) {
            $this->error_params = array('start' => $this->start, 'stop' => $this->stop);

            return false;
        }

        return true;
    }
}