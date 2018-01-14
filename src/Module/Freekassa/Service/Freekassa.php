<?php
class Krugozor_Module_Freekassa_Service_Freekassa
{
    const MERCHANT_URL = 'http://www.free-kassa.ru/merchant/cash.php?';

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
       $sign = md5(Krugozor_Registry::getInstance()->PAYMENTS['FREEKASSA_m'] . ':' .
            $request->getRequest('AMOUNT', 'decimal') . ':' .
            Krugozor_Registry::getInstance()->PAYMENTS['FREEKASSA_SECRET_2'] . ':' .
            $request->getRequest('MERCHANT_ORDER_ID', 'decimal'));

        return $sign === $request->getRequest('SIGN', 'string');
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
        $params['m'] = Krugozor_Registry::getInstance()->PAYMENTS['FREEKASSA_m'];
        $params['oa'] = self::getPayment($action);
        $params['o'] = '0';
        $params['lang'] = 'ru';
        $params['s'] = md5($params['m'] . ':' . $params['oa'] . ':' . Krugozor_Registry::getInstance()->PAYMENTS['FREEKASSA_SECRET_1'] . ':' . $params['o']);
        $params['us_ADVERT'] = $this->advert->getId();
        $params['us_ACTION'] = $action;

        if ($this->advert->getEmail()->getValue()) {
            $params['em'] = $this->advert->getEmail()->getValue();
        }

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