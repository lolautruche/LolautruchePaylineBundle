<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Tests\Event;

use Lolautruche\PaylineBundle\Event\PaymentNotificationEvent;
use Lolautruche\PaylineBundle\Payline\PaylineResult;
use PHPUnit_Framework_TestCase;
use Symfony\Component\HttpFoundation\Response;

class PaymentNotificationEventTest extends PHPUnit_Framework_TestCase
{
    public function testConstruct()
    {
        $paylineResult = new PaylineResult([]);
        $event = new PaymentNotificationEvent($paylineResult);
        self::assertSame($paylineResult, $event->getPaylineResult());
    }

    public function testSetGetResponse()
    {
        $event = new PaymentNotificationEvent(new PaylineResult([]));
        self::assertNull($event->getResponse());
        self::assertFalse($event->hasResponse());
        $response = new Response();
        $event->setResponse($response);
        self::assertTrue($event->hasResponse());
        self::assertSame($response, $event->getResponse());
    }

    public function testIsSuccessful()
    {
        // Successful result
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        $event = new PaymentNotificationEvent($result);
        self::assertTrue($event->isPaymentSuccessful());

        // Unsuccessful result
        $result = new PaylineResult([
            'result' => [
                'code' => '12345',
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        $event = new PaymentNotificationEvent($result);
        self::assertFalse($event->isPaymentSuccessful());
    }

    public function testIsCanceled()
    {
        // Canceled
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_CANCELED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        $event = new PaymentNotificationEvent($result);
        self::assertTrue($event->isPaymentCanceledByUser());

        // Not canceled
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        $event = new PaymentNotificationEvent($result);
        self::assertFalse($event->isPaymentCanceledByUser());
    }

    public function testIsDuplicate()
    {
        // Duplicate
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_DUPLICATE,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        $event = new PaymentNotificationEvent($result);
        self::assertTrue($event->isPaymentDuplicate());

        // Unsuccessful result
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        $event = new PaymentNotificationEvent($result);
        self::assertFalse($event->isPaymentDuplicate());
    }
}
