<?php
class Krugozor_Module_Captcha_Validator_Captcha extends Krugozor_Validator_Abstract
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
    public function __construct($request_value, $session_value)
    {
        $this->request_value = (string) $request_value;
        $this->session_value = (string) $session_value;
    }

    /**
     * @see Krugozor/Validator/Validator_Abstract#validate()
     */
    public function validate()
    {
        return (!empty($this->request_value) && !empty($this->session_value) && $this->session_value === $this->request_value);
    }
}