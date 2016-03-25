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

use Payline\PaylineSDK;

/**
 * Main Payline implementation, abstracting PaylineSDK.
 */
class Payline implements WebGatewayInterface
{
    /**
     * @var PaylineSDK
     */
    private $paylineSDK;

    /**
     * Default currency code.
     *
     * @var int
     */
    private $defaultCurrency;

    /**
     * Default contract number, reprenting payment mediums available.
     *
     * @var string
     */
    private $defaultContractNumber;

    public function __construct(PaylineSDK $paylineSDK, $defaultCurrency, $defaultContractNumber = null)
    {
        $this->paylineSDK = $paylineSDK;
        $this->defaultCurrency = $defaultCurrency;
        $this->defaultContractNumber = $defaultContractNumber;
    }

    /**
     * @return PaylineSDK
     */
    public function getPaylineSDK()
    {
        return $this->paylineSDK;
    }

    /**
     * {@inheritdoc}
     */
    public function initiateWebTransaction(WebTransaction $transaction)
    {
        $payment = [
            'amount' => $transaction->getAmount(),
            'currency' => $transaction->getCurrency() ?: $this->defaultCurrency,
            'action' => $transaction->getAction(),
            'mode' => $transaction->getMode(),
            'contractNumber' => $transaction->getContractNumber(),
        ];

        $order = [
            'ref' => $transaction->getOrderRef(),
            'amount' => $transaction->getOrderAmount() ?: $transaction->getAmount(),
            'currency' => $transaction->getOrderCurrency() ?: $transaction->getCurrency(),
            'date' => $transaction->getOrderDate(),
        ];
        if ($orderTaxes = $transaction->getOrderTaxes()) {
            $order['taxes'] = $orderTaxes;
        }
        if ($orderCountry = $transaction->getOrderCountry()) {
            $order['country'] = $orderCountry;
        }

        $params = array_merge_recursive([
            'payment' => $payment,
            'order' => $order,
        ], $transaction->getExtraOptions());

        $paylineResult = new PaylineResult($this->paylineSDK->doWebPayment($params));

        return $paylineResult;
    }

    /**
     * {@inheritdoc}
     */
    public function verifyWebTransaction($paymentToken)
    {
        $response = $this->paylineSDK->getWebPaymentDetails(['token' => $paymentToken]);

        return new PaylineResult($response);
    }
}
