<?php

namespace Krugozor\Framework\Module\Captcha\Validator;

use Krugozor\Framework\Validator\ValidatorAbstract;

class Captcha extends ValidatorAbstract
{
    protected $error_key = 'BAD_CAPTCHA';

    /**
     * Значение капчи, пришедшее "из формы", т.е. из Post.
     *
     * @var string
     */
    private $request_value;

    /**
     * Значение капчи из сесии.
     *
     * @var string
     */
    private $session_value;

    /**
     * @param string $request_value
     * @param string $session_value
     */
    public function __construct(string $request_value, string $session_value)
    {
        $this->request_value = $request_value;
        $this->session_value = $session_value;
    }

    /**
     * @return bool
     */
    public function validate(): bool
    {
        return (!empty($this->request_value) && !empty($this->session_value) && $this->session_value === $this->request_value);
    }
}