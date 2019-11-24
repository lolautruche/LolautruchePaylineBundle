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
     * Default confirmation URL the user will be redirected to after the payment.
     * It is an absolute URL.
     *
     * @var string
     */
    private $defaultConfirmationUrl;

    /**
     * Default URL where the user will be redirected to if the payment was unsuccessful.
     * It is an absolute URL.
     *
     * @var string
     */
    private $defaultErrorUrl;

    /**
     * PaylineController constructor.
     * @param EventDispatcherInterface $eventDispatcher
     * @param WebGatewayInterface      $payline
     * @param string                   $defaultConfirmationUrl
     * @param string                   $defaultErrorUrl
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, WebGatewayInterface $payline, $defaultConfirmationUrl, $defaultErrorUrl)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->payline = $payline;
        $this->defaultConfirmationUrl = $defaultConfirmationUrl;
        $this->defaultErrorUrl = $defaultErrorUrl;
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function paymentNotificationAction(Request $request)
    {
        $result = $this->payline->verifyWebTransaction($request->get('paylinetoken', $request->get('token')));
        $this->eventDispatcher->dispatch(new PaymentNotificationEvent($result), PaylineEvents::ON_NOTIFICATION);

        return new Response('OK');
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function backToShopAction(Request $request)
    {
        $result = $this->payline->verifyWebTransaction($request->get('paylinetoken', $request->get('token')));
        $event = new PaymentNotificationEvent($result);
        $this->eventDispatcher->dispatch($event, PaylineEvents::ON_BACK_TO_SHOP);

        if ($event->hasResponse()) {
            return $event->getResponse();
        }

        return new RedirectResponse($result->isSuccessful() ? $this->defaultConfirmationUrl : $this->defaultErrorUrl);
    }
}
