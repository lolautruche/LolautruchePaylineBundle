<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Controller;

use Lolautruche\PaylineBundle\Event\PaylineEvents;
use Lolautruche\PaylineBundle\Event\PaymentNotificationEvent;
use Lolautruche\PaylineBundle\Payline\WebGatewayInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaylineController
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;

    /**
     * @var WebGatewayInterface
     */
    private $payline;

    /**
     * Default confirmation URL the user will be redirect to after the payment.
     * It is an absolute URL.
     *
     * @var string
     */
    private $defaultConfirmationUrl;

    public function __construct(EventDispatcherInterface $eventDispatcher, WebGatewayInterface $payline, $defaultConfirmationUrl)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->payline = $payline;
        $this->defaultConfirmationUrl = $defaultConfirmationUrl;
    }

    public function paymentNotificationAction(Request $request)
    {
        $result = $this->payline->verifyWebTransaction($request->get('token'));
        $event = new PaymentNotificationEvent($result);
        $this->eventDispatcher->dispatch(PaylineEvents::ON_NOTIFICATION, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return new Response('OK');
    }

    public function backToShopAction(Request $request)
    {
        $result = $this->payline->verifyWebTransaction($request->get('token'));
        $event = new PaymentNotificationEvent($result);
        $this->eventDispatcher->dispatch(PaylineEvents::ON_BACK_TO_SHOP, $event);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return new RedirectResponse($this->defaultConfirmationUrl);
    }
}
