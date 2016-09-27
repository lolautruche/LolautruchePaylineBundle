<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Tests\Payline;

use DateTime;
use Lolautruche\PaylineBundle\Event\PaylineEvents;
use Lolautruche\PaylineBundle\Event\ResultEvent;
use Lolautruche\PaylineBundle\Event\WebTransactionEvent;
use Lolautruche\PaylineBundle\Payline\Payline;
use Lolautruche\PaylineBundle\Payline\PaylineResult;
use Lolautruche\PaylineBundle\Payline\WebTransaction;
use PHPUnit_Framework_TestCase;

class PaylineTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var \Payline\PaylineSDK|\PHPUnit_Framework_MockObject_MockObject
     */
    private $sdk;

    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @inheritDoc
     */
    protected function setUp()
    {
        parent::setUp();
        $this->sdk = $this->createMock('\Payline\PaylineSDK');
        $this->eventDispatcher = $this->createMock('\Symfony\Component\EventDispatcher\EventDispatcherInterface');
    }

    public function testGetPaylineSdk()
    {
        $payline = new Payline($this->sdk, $this->eventDispatcher, WebTransaction::CURRENCY_EUR, 'return', 'cancel', 'notification');
        self::assertSame($this->sdk, $payline->getPaylineSDK());
    }

    public function testInitiateWebTransactionAllDefaults()
    {
        $defaultCurrency = WebTransaction::CURRENCY_EUR;
        $defaultReturnUrl = 'return';
        $defaultCancelUrl = 'cancel';
        $defaultNotificationUrl = 'notification';
        $defaultContractNumber = '1234567';
        $payline = new Payline(
            $this->sdk, 
            $this->eventDispatcher, 
            $defaultCurrency, 
            $defaultReturnUrl, 
            $defaultCancelUrl, 
            $defaultNotificationUrl,
            $defaultContractNumber
        );
        
        $amount = 100;
        $orderRef = '123abc456';
        $orderDate = new DateTime();
        $transaction = new WebTransaction($amount, $orderRef, $orderDate);
        
        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(PaylineEvents::PRE_WEB_TRANSACTION_INITIATE, $this->equalTo(new WebTransactionEvent($transaction)));

        $returnHash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ]
        ];
        $this->sdk
            ->expects($this->once())
            ->method('doWebPayment')
            ->with([
                'payment' => [
                    'amount' => $amount,
                    'currency' => $defaultCurrency,
                    'action' => $transaction->getAction(),
                    'mode' => $transaction->getMode(),
                    'contractNumber' => $defaultContractNumber,
                ],
                'order' => [
                    'ref' => $orderRef,
                    'amount' => $amount,
                    'currency' => $defaultCurrency,
                    'date' => $orderDate->format('d/m/Y H:i'),
                ],
                'returnURL' => $defaultReturnUrl,
                'cancelURL' => $defaultCancelUrl,
                'notificationURL' => $defaultNotificationUrl,
            ])
            ->willReturn($returnHash);

        $result = new PaylineResult($returnHash);
        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(PaylineEvents::POST_WEB_TRANSACTION_INITIATE, $this->equalTo(new ResultEvent($result)));

        self::assertEquals($result, $payline->initiateWebTransaction($transaction));
    }

    public function testInitiateWebPaymentCustom()
    {
        $defaultCurrency = WebTransaction::CURRENCY_EUR;
        $returnUrl = 'return';
        $cancelUrl = 'cancel';
        $notificationUrl = 'notification';
        $defaultContractNumber = '1234567';
        $payline = new Payline(
            $this->sdk,
            $this->eventDispatcher,
            $defaultCurrency,
            $returnUrl,
            $cancelUrl,
            $notificationUrl,
            $defaultContractNumber
        );

        $amount = 100;
        $orderAmount = 120;
        $orderRef = '123abc456';
        $orderDate = new DateTime();
        $transaction = new WebTransaction($amount, $orderRef, $orderDate);
        $currency = WebTransaction::CURRENCY_CAD;
        $orderCurrency = WebTransaction::CURRENCY_DOLLAR;
        $contractNumber = '789123000';
        $orderTaxes = 10;
        $orderCountry = 'FR';
        $buyerEmail = 'foo@bar.com';
        $transaction
            ->setCurrency($currency)
            ->setOrderCurrency($orderCurrency)
            ->setOrderAmount($orderAmount)
            ->setOrderTaxes($orderTaxes)
            ->setOrderCountry($orderCountry)
            ->setContractNumber($contractNumber)
            ->setExtraOptions([
                'buyer' => ['email' => $buyerEmail],
            ])
            ->addPrivateData('foo', 'bar')
            ->addPrivateData('baz', 'biz');

        $this->eventDispatcher
            ->expects($this->at(0))
            ->method('dispatch')
            ->with(PaylineEvents::PRE_WEB_TRANSACTION_INITIATE, $this->equalTo(new WebTransactionEvent($transaction)));

        $this->sdk
            ->expects($this->at(0))
            ->method('addPrivateData')
            ->with(['key' => 'foo', 'value' => 'bar']);
        $this->sdk
            ->expects($this->at(1))
            ->method('addPrivateData')
            ->with(['key' => 'baz', 'value' => 'biz']);

        $returnHash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ]
        ];
        $this->sdk
            ->expects($this->at(2))
            ->method('doWebPayment')
            ->with([
                'payment' => [
                    'amount' => $amount,
                    'currency' => $currency,
                    'action' => $transaction->getAction(),
                    'mode' => $transaction->getMode(),
                    'contractNumber' => $contractNumber,
                ],
                'order' => [
                    'ref' => $orderRef,
                    'amount' => $orderAmount,
                    'currency' => $orderCurrency,
                    'date' => $orderDate->format('d/m/Y H:i'),
                    'taxes' => $orderTaxes,
                    'country' => $orderCountry,
                ],
                'buyer' => ['email' => $buyerEmail],
                'returnURL' => $returnUrl,
                'cancelURL' => $cancelUrl,
                'notificationURL' => $notificationUrl,
            ])
            ->willReturn($returnHash);

        $result = new PaylineResult($returnHash);
        $this->eventDispatcher
            ->expects($this->at(1))
            ->method('dispatch')
            ->with(PaylineEvents::POST_WEB_TRANSACTION_INITIATE, $this->equalTo(new ResultEvent($result)));

        self::assertEquals($result, $payline->initiateWebTransaction($transaction));
    }

    public function testVerifyWebTransaction()
    {
        $token = md5(microtime(true));
        $defaultCurrency = WebTransaction::CURRENCY_EUR;
        $returnUrl = 'return';
        $cancelUrl = 'cancel';
        $notificationUrl = 'notification';
        $payline = new Payline(
            $this->sdk,
            $this->eventDispatcher,
            $defaultCurrency,
            $returnUrl,
            $cancelUrl,
            $notificationUrl
        );

        $returnHash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ]
        ];
        $this->sdk
            ->expects($this->once())
            ->method('getWebPaymentDetails')
            ->with(['token' => $token])
            ->willReturn($returnHash);
        $result = new PaylineResult($returnHash);
        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(PaylineEvents::WEB_TRANSACTION_VERIFY, $this->equalTo(new ResultEvent($result)));

        self::assertEquals($result, $payline->verifyWebTransaction($token));
    }
    
    public function testDoRefund()
    {
        $differedActionDate = new DateTime();
        $transactionId = '123456789';
        $token = md5(microtime(true));
        $defaultCurrency = WebTransaction::CURRENCY_EUR;
        $returnUrl = 'return';
        $cancelUrl = 'cancel';
        $notificationUrl = 'notification';
        $payline = new Payline(
            $this->sdk,
            $this->eventDispatcher,
            $defaultCurrency,
            $returnUrl,
            $cancelUrl,
            $notificationUrl
        );

        $returnHash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
            'transaction' => [
                'id' => $transactionId
            ],
            'payment' => [
                'amount' => '10.00',
                'currency' => '978',
                'mode' => 'CPT',
                'contractNumber' => '123456',
                'differedActionDate' => $differedActionDate,
                'method' => 'CB'
            ],
            'privateDataList' => [
                'privateData' => [
                    [
                        'key' => 'foo',
                        'value' => 'bar'
                    ],
                    [
                        'key' => 'foo',
                        'value' => 'bar'
                    ]
                ]
            ]
        ];
        $this->sdk
            ->expects($this->once())
            ->method('getWebPaymentDetails')
            ->with(['token' => $token])
            ->willReturn($returnHash);

        $this->sdk
            ->expects($this->any())
            ->method('addPrivateData');

        $this->sdk
            ->expects($this->once())
            ->method('doRefund')
            ->with([
                'transactionID' => $transactionId,
                'payment' => [
                    'amount' => '10.00',
                    'currency' => '978',
                    'action' => '421',
                    'mode' => 'CPT',
                    'contractNumber' => '123456',
                    'differedActionDate' => $differedActionDate,
                    'method' => 'CB'
                ],
                'comment' => '',
                'sequenceNumber' => 0
            ])
            ->willReturn($returnHash);

        $result = new PaylineResult($returnHash);

        self::assertEquals($result, $payline->doRefund($token));
    }
}
