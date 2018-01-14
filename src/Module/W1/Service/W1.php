<?php
class Krugozor_Module_W1_Service_W1
{
    const MERCHANT_URL = 'https://wl.walletone.com/checkout/checkout/Index';

    // Размещение объявления
    const ACTION_ACTIVATE = 1;

    // Выделение объявления
    const ACTION_TOP = 2;

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
     * Возвращает форму с параметрами.
     *
     * @param int $action тип услуги
     * @return string
     */
    public function getMerchantForm($action)
    {
        $str = "<form id='w1_" . $this->advert->getId() . "' action='" . self::MERCHANT_URL . "' method='POST'>";

        foreach($this->getParams($action) as $key => $val) {
            if (is_array($val)) {
                foreach($val as $value) {
                    $str .= "<input type='hidden' name='$key' value='$value'/>";
                }
            } else {
                $str .= "<input type='hidden' name='$key' value='$val'/>";
            }
        }

        $str .=  "</form>";

        return $str;
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

        $params['WMI_MERCHANT_ID'] = Krugozor_Registry::getInstance()->PAYMENTS['WMI_MERCHANT_ID'];
        $params['WMI_PAYMENT_AMOUNT'] = self::getPayment($action);
        $params['WMI_CURRENCY_ID'] = '643';
        $params['WMI_PAYMENT_NO'] = '0';
        $params['WMI_DESCRIPTION'] = self::getDescription($action) . $this->advert->getId();
        $params['WMI_SUCCESS_URL'] = Krugozor_Registry::getInstance()->HOSTINFO['HOST_URL'] . '/w1/success/';
        $params['WMI_FAIL_URL'] = Krugozor_Registry::getInstance()->HOSTINFO['HOST_URL'] . '/w1/success/';
        $params['WMI_CULTURE_ID'] = 'ru-RU';

        $date = new Datetime();
        $date->add(new DateInterval('P6M'));
        $params['WMI_EXPIRED_DATE'] = $date->format(DateTime::ISO8601);

        $params['ADVERT'] = $this->advert->getId();
        $params['ACTION'] = $action;

        if ($this->advert->getEmail()->getValue()) {
            $params['WMI_CUSTOMER_EMAIL'] = $this->advert->getEmail()->getValue();
        }

        //Секретный ключ интернет-магазина
        $key = "7b7b7a3835455b6b68635f424c703133306d66784367474e785a35";

        //Сортировка значений внутри полей
        foreach($params as $name => $val) {
            if(is_array($val)) {
                usort($val, "strcasecmp");
                $params[$name] = $val;
            }
        }

        // Формирование сообщения, путем объединения значений формы,
        // отсортированных по именам ключей в порядке возрастания.
        uksort($params, "strcasecmp");
        $fieldValues = "";

        foreach($params as $value) {
            if(is_array($value))
                foreach($value as $v) {
                    //Конвертация из текущей кодировки (UTF-8)
                    //необходима только если кодировка магазина отлична от Windows-1251
                    $v = iconv("utf-8", "windows-1251", $v);
                    $fieldValues .= $v;
                }
            else {
                //Конвертация из текущей кодировки (UTF-8)
                //необходима только если кодировка магазина отлична от Windows-1251
                $value = iconv("utf-8", "windows-1251", $value);
                $fieldValues .= $value;
            }
        }

        // Формирование значения параметра WMI_SIGNATURE, путем
        // вычисления отпечатка, сформированного выше сообщения,
        // по алгоритму MD5 и представление его в Base64
        $params["WMI_SIGNATURE"] = base64_encode(pack("H*", md5($fieldValues . $key)));

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