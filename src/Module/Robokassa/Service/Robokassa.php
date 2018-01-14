<?php
class Krugozor_Module_Robokassa_Service_Robokassa
{
    const MERCHANT_URL = 'http://auth.robokassa.ru/Merchant/Index.aspx?';
    const MERCHANT_FORM_URL = 'https://auth.robokassa.ru/Merchant/PaymentForm/FormL.js?';

    // Размещение объявления
    const ACTION_ACTIVATE = 1;

    // Выделение объявления
    const ACTION_TOP = 2;

    // Спецпредложение
    const ACTION_SPECIAL = 3;

    /**
     * @var Krugozor_Module_Advert_Model_Advert
     */
    private $advert;

    /**
     * @param Krugozor_Module_Advert_Model_Advert $advert
     */
    public function setAdvert(Krugozor_Module_Advert_Model_Advert $advert)
    {
        $this->advert = $advert;
    }

    /**
     * @param int $action тип услуги
     * @return string
     */
    public function getMerchantFormUrl($action)
    {
        return self::MERCHANT_FORM_URL . http_build_query($this->getParams($action), '', '&');
    }

    /**
     * @param int $action тип услуги
     * @return string
     */
    public function getMerchantUrl($action)
    {
        return self::MERCHANT_URL . http_build_query($this->getParams($action), '', '&');
    }

    /**
     * Проверка для ResultURL.
     *
     * @param Krugozor_Http_Request $request
     * @return boolean
     */
    public function checkResult(Krugozor_Http_Request $request)
    {
        $OutSum = $request->getGet('OutSum', 'string');
        $InvId = $request->getGet('InvId', 'string');
        $SHP_ADVERT = $request->getGet('SHP_ADVERT', 'decimal');
        $SHP_ACTION = $request->getGet('SHP_ACTION', 'decimal');
        $SignatureValue = $request->getGet('SignatureValue', 'string');

        return strtoupper(md5("$OutSum:$InvId:" . Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_PASSWORD_2'] . ":SHP_ACTION=$SHP_ACTION:SHP_ADVERT=$SHP_ADVERT")) == strtoupper($SignatureValue);
    }

    /**
     * Проверка для SuccessURL.
     *
     * @param Krugozor_Http_Request $request
     * @return boolean
     */
    public function checkSuccess(Krugozor_Http_Request $request)
    {
        $OutSum = $request->getGet('OutSum', 'string');
        $InvId = $request->getGet('InvId', 'string');
        $SHP_ADVERT = $request->getGet('SHP_ADVERT', 'string');
        $SHP_ACTION = $request->getGet('SHP_ACTION', 'decimal');
        $SignatureValue = $request->getGet('SignatureValue', 'string');

        return strtoupper(md5("$OutSum:$InvId:" . Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_PASSWORD_1'] . ":SHP_ACTION=$SHP_ACTION:SHP_ADVERT=$SHP_ADVERT")) == strtoupper($SignatureValue);
    }

    /**
     * Возвращает массив параметров, необходимый для процедуры оплаты.
     *
     * @param int $action тип услуги
     * @return array
     */
    private function getParams($action)
    {
        if ($this->advert === null) {
            throw new InvalidArgumentException(__METHOD__ . ': Не передан объект объявления');
        }

        $this->checkAction($action);

        $params = array();

        $params['SignatureValue'] = md5(
            Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_MerchantLogin'] . ':' .
            self::getPayment($action) . ':0:' .
            Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_PASSWORD_1'] . ':' .
            'SHP_ACTION=' . $action . ':' .
            'SHP_ADVERT=' . $this->advert->getId()
        );

        $params['Description'] = self::getDescription($action) . $this->advert->getId();
        $params['OutSum'] = self::getPayment($action);
        $params['DefaultSum'] = self::getPayment($action);
        $params['MerchantLogin'] = Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_MerchantLogin'];
        $params['Culture'] = Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_Culture'];
        $params['Encoding'] = Krugozor_Registry::getInstance()->PAYMENTS['ROBOKASSA_Encoding'];
        $params['InvoiceID'] = 0;
        $params['SHP_ADVERT'] = $this->advert->getId();
        $params['SHP_ACTION'] = $action;

        if ($this->advert->getEmail()->getValue()) {
            $params['Email'] = $this->advert->getEmail()->getValue();
        }

        $date = new Datetime();
        $date->add(new DateInterval('P6M'));

        $params['ExpirationDate'] = $date->format(DateTime::ISO8601);

        return $params;
    }

    /**
     * Возвращает сумму для оплаты услуги.
     *
     * @param int $action
     */
    private static function getPayment($action)
    {
        self::checkAction($action);

        switch ($action) {
            case self::ACTION_ACTIVATE:
                return Krugozor_Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_ACTIVATE'];

            case self::ACTION_TOP:
                return Krugozor_Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_TOP'];

            case self::ACTION_SPECIAL:
                return Krugozor_Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_SPECIAL'];

            default:
                return null;
        }
    }

    /**
     * Возвращает описание услуги.
     *
     * @param int $action
     */
    private static function getDescription($action)
    {
        self::checkAction($action);

        switch ($action) {
            case self::ACTION_ACTIVATE:
                return Krugozor_Registry::getInstance()->PAYMENTS['DESCRIPTION_ACTION_ACTIVATE'];

            case self::ACTION_TOP:
                return Krugozor_Registry::getInstance()->PAYMENTS['DESCRIPTION_ACTION_TOP'];

            case self::ACTION_SPECIAL:
                return Krugozor_Registry::getInstance()->PAYMENTS['DESCRIPTION_ACTION_SPECIAL'];

            default:
                return null;
        }
    }

    /**
     * Проверяет переданный action.
     *
     * @param string|int $action
     * @return boolean
     */
    private static function checkAction($action)
    {
        $constants = (new ReflectionClass(__CLASS__))->getConstants();

        foreach ($constants as $const_name => $value) {
            if (!preg_match('/^ACTION_.+/i', $const_name)) {
                unset($constants[$const_name]);
            }
        }

        if (!in_array((int)$action, array_values($constants))) {
            throw new InvalidArgumentException('Неизвестный тип услуги: ' . print_r($action, 1));
        }
    }
}