<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Tests\Controller;

use Lolautruche\PaylineBundle\Controller\PaylineController;
use Lolautruche\PaylineBundle\Event\PaylineEvents;
use Lolautruche\PaylineBundle\Event\PaymentNotificationEvent;
use Lolautruche\PaylineBundle\Payline\PaylineResult;
use Lolautruche\PaylineBundle\Payline\WebGatewayInterface;
use PHPUnit\Framework\TestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaylineControllerTest extends TestCase
{
    /**
     * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $eventDispatcher;

    /**
     * @var \Lolautruche\PaylineBundle\Payline\WebGatewayInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $paylineGateway;

    /**
     * @inheritDoc
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->eventDispatcher = $this->createMock(EventDispatcherInterface::class);
        $this->paylineGateway = $this->createMock(WebGatewayInterface::class);
    }

    public function testPaymentNotificationAction()
    {
        $token = md5(microtime(true));
        $request = new Request();
        $request->query->set('paylinetoken', $token);
        $result = new PaylineResult([]);
        $this->paylineGateway
            ->expects($this->once())
            ->method('verifyWebTransaction')
            ->with($token)
            ->willReturn($result);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(PaylineEvents::ON_NOTIFICATION, new PaymentNotificationEvent($result));

        $controller = new PaylineController($this->eventDispatcher, $this->paylineGateway, 'confirmation', 'error');
        self::assertEquals(new Response('OK'), $controller->paymentNotificationAction($request));
    }

    public function testBackToShopActionDefaultRedirect()
    {
        $token = md5(microtime(true));
        $request = new Request();
        $request->query->set('paylinetoken', $token);
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ]
        ]);
        $this->paylineGateway
            ->expects($this->once())
            ->method('verifyWebTransaction')
            ->with($token)
            ->willReturn($result);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(PaylineEvents::ON_BACK_TO_SHOP, new PaymentNotificationEvent($result));

        $defaultConfirmationUrl = 'confirmation';
        $defaultErrorUrl = 'error';
        $controller = new PaylineController($this->eventDispatcher, $this->paylineGateway, $defaultConfirmationUrl, $defaultErrorUrl);
        self::assertEquals(new RedirectResponse($defaultConfirmationUrl), $controller->backToShopAction($request));
    }

    public function testBackToShopActionDefaultRedirectError()
    {
        $token = md5(microtime(true));
        $request = new Request();
        $request->query->set('paylinetoken', $token);
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_INTERNAL_ERROR,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ]
        ]);
        $this->paylineGateway
            ->expects($this->once())
            ->method('verifyWebTransaction')
            ->with($token)
            ->willReturn($result);

        $this->eventDispatcher
            ->expects($this->once())
            ->method('dispatch')
            ->with(PaylineEvents::ON_BACK_TO_SHOP, new PaymentNotificationEvent($result));

        $defaultConfirmationUrl = 'confirmation';
        $defaultErrorUrl = 'error';
        $controller = new PaylineController($this->eventDispatcher, $this->paylineGateway, $defaultConfirmationUrl, $defaultErrorUrl);
        self::assertEquals(new RedirectResponse($defaultErrorUrl), $controller->backToShopAction($request));
    }

    public function testBackToShopActionCustomResponse()
    {
        $token = md5(microtime(true));
        $request = new Request();
        $request->query->set('paylinetoken', $token);
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ]
        ]);
        $this->paylineGateway
            ->expects($this->once())
            ->method('verifyWebTransaction')
            ->with($token)
            ->willReturn($result);

        $customResponse = new Response('custom');
        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(PaylineEvents::ON_BACK_TO_SHOP, function (PaymentNotificationEvent $event) use ($customResponse) {
            $event->setResponse($customResponse);
        });
        $defaultConfirmationUrl = 'confirmation';
        $defaultErrorUrl = 'error';
        $controller = new PaylineController($eventDispatcher, $this->paylineGateway, $defaultConfirmationUrl, $defaultErrorUrl);
        self::assertEquals($customResponse, $controller->backToShopAction($request));
    }
}
