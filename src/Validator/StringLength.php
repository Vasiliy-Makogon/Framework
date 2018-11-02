<?php

namespace Krugozor\Framework\Validator;

use Krugozor\Framework\Statical\Strings;

/**
 * Проверка значения (строки) на определенную длинну.
 */
class StringLength extends ValidatorAbstract
{
    /**
     * @var string
     */
    protected $error_key = 'INVALID_STRING_LENGTH';

    /**
     * @var int
     */
    const MD5_MAX_LENGTH = 32;

    /**
     * @var int
     */
    const VARCHAR_MAX_LENGTH = 255;

    /**
     * @var int
     */
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
     * @return StringLength
     */
    public function setStart(int $start): self
    {
        $this->start = $start;

        return $this;
    }

    /**
     * @param int $stop
     * @return StringLength
     */
    public function setStop(int $stop): self
    {
        $this->stop = $stop;

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

        $len = mb_strlen($this->value);

        if (!($len >= $this->start && $len <= $this->stop)) {
            $this->error_params = array('start' => $this->start, 'stop' => $this->stop);

            return false;
        }

        return true;
    }
}