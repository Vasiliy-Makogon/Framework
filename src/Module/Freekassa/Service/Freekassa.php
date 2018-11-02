<?php

namespace Krugozor\Framework\Module\Freekassa\Service;

use Krugozor\Framework\Module\Advert\Model\Advert;
use Krugozor\Framework\Registry;

class Freekassa
{
    const MERCHANT_URL = 'http://www.free-kassa.ru/merchant/cash.php?';

    // Размещение объявления
    const ACTION_ACTIVATE = 1;

    // Выделение объявления
    const ACTION_TOP = 2;

    // Спецпредложение
    const ACTION_SPECIAL = 3;

    /**
     * @var Advert
     */
    private $advert;

    /**
     * @param Advert $advert
     */
    public function setAdvert(Advert $advert)
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
     * @param Request $request
     * @return boolean
     */
    public function checkResult(Request $request)
    {
       $sign = md5(Registry::getInstance()->PAYMENTS['FREEKASSA_m'] . ':' .
            $request->getRequest('AMOUNT', 'decimal') . ':' .
            Registry::getInstance()->PAYMENTS['FREEKASSA_SECRET_2'] . ':' .
            $request->getRequest('MERCHANT_ORDER_ID', 'decimal'));

        return $sign === $request->getRequest('SIGN', 'string');
    }

    /**
     * Возвращает массив параметров, необходимый для процедуры оплаты.
     *
     * @param int $action
     * @return array
     */
    private function getParams(int $action): array
    {
        if ($this->advert === null) {
            throw new \InvalidArgumentException(__METHOD__ . ': Не передан объект объявления');
        }

        $this->checkAction($action);

        $params = array();
        $params['m'] = Registry::getInstance()->PAYMENTS['FREEKASSA_m'];
        $params['oa'] = self::getPayment($action);
        $params['o'] = '0';
        $params['lang'] = 'ru';
        $params['s'] = md5($params['m'] . ':' . $params['oa'] . ':' . Registry::getInstance()->PAYMENTS['FREEKASSA_SECRET_1'] . ':' . $params['o']);
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
     * @return null|string
     */
    private static function getPayment(int $action): ?string
    {
        self::checkAction($action);

        switch ($action) {
            case self::ACTION_ACTIVATE:
                return Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_ACTIVATE'];

            case self::ACTION_TOP:
                return Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_TOP'];

            case self::ACTION_SPECIAL:
                return Registry::getInstance()->PAYMENTS['PAYMENT_ACTION_SPECIAL'];

            default:
                return null;
        }
    }

    /**
     * Возвращает описание услуги.
     *
     * @param int $action
     * @return null|string
     */
    private static function getDescription(int $action): ?string
    {
        self::checkAction($action);

        switch ($action) {
            case self::ACTION_ACTIVATE:
                return Registry::getInstance()->PAYMENTS['DESCRIPTION_ACTION_ACTIVATE'];

            case self::ACTION_TOP:
                return Registry::getInstance()->PAYMENTS['DESCRIPTION_ACTION_TOP'];

            case self::ACTION_SPECIAL:
                return Registry::getInstance()->PAYMENTS['DESCRIPTION_ACTION_SPECIAL'];

            default:
                return null;
        }
    }

    /**
     * Проверяет переданный action.
     *
     * @param int $action
     */
    private static function checkAction(int $action)
    {
        $constants = (new \ReflectionClass(__CLASS__))->getConstants();

        foreach ($constants as $const_name => $value) {
            if (!preg_match('/^ACTION_.+/i', $const_name)) {
                unset($constants[$const_name]);
            }
        }

        if (!in_array((int)$action, array_values($constants))) {
            throw new \InvalidArgumentException('Неизвестный тип услуги: ' . print_r($action, 1));
        }
    }
}