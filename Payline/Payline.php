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

use Lolautruche\PaylineBundle\Event\PaylineEvents;
use Lolautruche\PaylineBundle\Event\ResultEvent;
use Lolautruche\PaylineBundle\Event\WebTransactionEvent;
use Payline\PaylineSDK;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

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
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * Default currency code.
     *
     * @var int
     */
    private $defaultCurrency;

    /**
     * @var string
     */
    private $defaultReturnUrl;

    /**
     * @var string
     */
    private $defaultCancelUrl;

    /**
     * @var string
     */
    private $defaultNotificationUrl;

    /**
     * Default contract number, reprenting payment mediums available.
     *
     * @var string
     */
    private $defaultContractNumber;

    public function __construct(
        PaylineSDK $paylineSDK,
        EventDispatcherInterface $eventDispatcher,
        $defaultCurrency,
        $defaultReturnUrl,
        $defaultCancelUrl,
        $defaultNotificationUrl,
        $defaultContractNumber = null
    ) {
        $this->paylineSDK = $paylineSDK;
        $this->eventDispatcher = $eventDispatcher;
        $this->defaultCurrency = $defaultCurrency;
        $this->defaultReturnUrl = $defaultReturnUrl;
        $this->defaultCancelUrl = $defaultCancelUrl;
        $this->defaultNotificationUrl = $defaultNotificationUrl;
        $this->defaultContractNumber = (string) $defaultContractNumber;
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
        $this->eventDispatcher->dispatch(
            PaylineEvents::PRE_WEB_TRANSACTION_INITIATE,
            new WebTransactionEvent($transaction)
        );

        $payment = [
            'amount' => $transaction->getAmount(),
            'currency' => $transaction->getCurrency() ?: $this->defaultCurrency,
            'action' => $transaction->getAction(),
            'mode' => $transaction->getMode(),
            'contractNumber' => (string) $transaction->getContractNumber() ?: $this->defaultContractNumber,
        ];

        $order = [
            'ref' => $transaction->getOrderRef(),
            'amount' => $transaction->getOrderAmount() ?: $transaction->getAmount(),
            'currency' => $transaction->getOrderCurrency() ?: $transaction->getCurrency() ?: $this->defaultCurrency,
            'date' => $transaction->getOrderDate()->format('d/m/Y H:i'),
        ];
        if ($orderTaxes = $transaction->getOrderTaxes()) {
            $order['taxes'] = $orderTaxes;
        }
        if ($orderCountry = $transaction->getOrderCountry()) {
            $order['country'] = $orderCountry;
        }

        // Merge options with extra options provided in the transaction.
        $params = array_merge_recursive([
            'payment' => $payment,
            'order' => $order,
            'returnURL' => $this->defaultReturnUrl,
            'cancelURL' => $this->defaultCancelUrl,
            'notificationURL' => $this->defaultNotificationUrl,
        ], $transaction->getExtraOptions());

        // Add private data.
        foreach ($transaction->getPrivateData() as $key => $value) {
            $this->paylineSDK->addPrivateData(['key' => $key, 'value' => $value]);
        }

        $paylineResult = new PaylineResult($this->paylineSDK->doWebPayment($params));
        $this->eventDispatcher->dispatch(
            PaylineEvents::POST_WEB_TRANSACTION_INITIATE,
            new ResultEvent($paylineResult)
        );

        return $paylineResult;
    }

    /**
     * {@inheritdoc}
     */
    public function verifyWebTransaction($paymentToken)
    {
        $response = $this->paylineSDK->getWebPaymentDetails(['token' => $paymentToken]);

        $paylineResult = new PaylineResult($response);
        $this->eventDispatcher->dispatch(
            PaylineEvents::WEB_TRANSACTION_VERIFY,
            new ResultEvent($paylineResult)
        );

        return $paylineResult;
    }
}
