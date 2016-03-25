<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Payline;

use DateTime;

/**
 * Transaction class for web payments (i.e. doWebPayment requests).
 */
class WebTransaction
{
    const CURRENCY_EUR = 978;
    const CURRENCY_DOLLAR = 840;
    const CURRENCY_CHF = 756;
    const CURRENCY_POUND = 826;
    const CURRENCY_CAD = 124;

    const PAYMENT_ACTION_AUTHORIZATION = 100;
    const PAYMENT_ACTION_AUTHORIZATION_CAPTURE = 101;

    const PAYMENT_MODE_CASH = 'CPT';
    const PAYMENT_MODE_DIFFERED = 'DIF';
    const PAYMENT_MODE_RECURRENT = 'REC';
    const PAYMENT_MODE_MULTIPLE = 'NX';

    /**
     * Payment amount, in the smallest currency unit (e.g. 145 for 1.45€)
     *
     * @var int
     */
    private $amount;

    /**
     * Currency code, if different than the default one.
     * One of the CURRENCY_* constants.
     *
     * @var int
     */
    private $currency;

    /**
     * Payment action code.
     *
     * @var int
     */
    private $action = self::PAYMENT_ACTION_AUTHORIZATION_CAPTURE;

    /**
     * Payment mode.
     * One of the PAYEMNT_MODE_* constants.
     *
     * @var string
     */
    private $mode = self::PAYMENT_MODE_CASH;

    /**
     * VAD contract number to use, if different than the default one.
     * This contract represents payment mediums one may use (e.g. VISA, Mastercard...).
     *
     * @var string
     */
    private $contractNumber;

    /**
     * Reference to the order.
     * Must be unique as it is used for duplicates control.
     *
     * @var mixed
     */
    private $orderRef;

    /**
     * Order amount in the smallest currency unit, if different than $amount.
     *
     * @var int
     */
    private $orderAmount;

    /**
     * Currency code of the order, if different than $currency.
     *
     * @var int
     */
    private $orderCurrency;

    /**
     * Tax amount for the order, in the smallest currency unit.
     *
     * @var int
     */
    private $orderTaxes;

    /**
     * Country code (e.g. "FR").
     *
     * @var string
     */
    private $orderCountry;

    /**
     * The order date.
     *
     * @var DateTime
     */
    private $orderDate;

    /**
     * Extra options to add to the payment.
     *
     * It is basically a hash representation of SOAP parameters.
     * Example : "payment.differedActionDate" parameter is represented as
     * ```
     * [
     *     "payment" => [
     *         "differedActionDate" => "24/03/2016"
     *     ]
     * ]
     * ```
     *
     * See Payline documentation what you can set.
     *
     * @var array
     */
    private $extraOptions = [];

    /**
     * Payline session token.
     *
     * @var string
     */
    private $token;

    public function __construct($amount, $orderRef, DateTime $orderDate)
    {
        $this->amount = $amount;
        $this->orderRef = $orderRef;
        $this->orderDate = $orderDate;
    }

    /**
     * @return int
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     * @return WebTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;
        return $this;
    }

    /**
     * @return int
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param int $currency
     * @return WebTransaction
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
        return $this;
    }

    /**
     * @return int
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param int $action
     * @return WebTransaction
     */
    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @param string $mode
     * @return WebTransaction
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
        return $this;
    }

    /**
     * @return string
     */
    public function getContractNumber()
    {
        return $this->contractNumber;
    }

    /**
     * @param string $contractNumber
     * @return WebTransaction
     */
    public function setContractNumber($contractNumber)
    {
        $this->contractNumber = $contractNumber;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getOrderRef()
    {
        return $this->orderRef;
    }

    /**
     * @param mixed $orderRef
     * @return WebTransaction
     */
    public function setOrderRef($orderRef)
    {
        $this->orderRef = $orderRef;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderAmount()
    {
        return $this->orderAmount;
    }

    /**
     * @param int $orderAmount
     * @return WebTransaction
     */
    public function setOrderAmount($orderAmount)
    {
        $this->orderAmount = $orderAmount;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderCurrency()
    {
        return $this->orderCurrency;
    }

    /**
     * @param int $orderCurrency
     * @return WebTransaction
     */
    public function setOrderCurrency($orderCurrency)
    {
        $this->orderCurrency = $orderCurrency;
        return $this;
    }

    /**
     * @return int
     */
    public function getOrderTaxes()
    {
        return $this->orderTaxes;
    }

    /**
     * @param int $orderTaxes
     * @return WebTransaction
     */
    public function setOrderTaxes($orderTaxes)
    {
        $this->orderTaxes = $orderTaxes;
        return $this;
    }

    /**
     * @return string
     */
    public function getOrderCountry()
    {
        return $this->orderCountry;
    }

    /**
     * @param string $orderCountry
     * @return WebTransaction
     */
    public function setOrderCountry($orderCountry)
    {
        $this->orderCountry = $orderCountry;
        return $this;
    }

    /**
     * @return DateTime
     */
    public function getOrderDate()
    {
        return $this->orderDate;
    }

    /**
     * @param DateTime $orderDate
     * @return WebTransaction
     */
    public function setOrderDate(DateTime $orderDate)
    {
        $this->orderDate = $orderDate;
        return $this;
    }

    /**
     * @return array
     */
    public function getExtraOptions()
    {
        return $this->extraOptions;
    }

    /**
     * @param array $extraOptions
     * @return WebTransaction
     */
    public function setExtraOptions(array $extraOptions)
    {
        $this->extraOptions = $extraOptions;
        return $this;
    }

    public function addExtraOption($key, $value)
    {
        $this->extraOptions[$key] = $value;
        return $this;
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param string $token
     */
    public function setToken(string $token)
    {
        $this->token = $token;
    }
}
