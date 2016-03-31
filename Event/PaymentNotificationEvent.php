<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Event;

use Lolautruche\PaylineBundle\Payline\PaylineResult;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class PaymentNotificationEvent extends Event
{
    /**
     * @var PaylineResult
     */
    private $paylineResult;

    /**
     * @var Response
     */
    private $response;

    public function __construct(PaylineResult $paylineResult)
    {
        $this->paylineResult = $paylineResult;
    }

    /**
     * @return PaylineResult
     */
    public function getPaylineResult()
    {
        return $this->paylineResult;
    }

    /**
     * Indicates if payment was successful or not.
     * Proxy to PaylineResult::isSuccessful().
     *
     * @return bool
     */
    public function isPaymentSuccessful()
    {
        return $this->paylineResult->isSuccessful();
    }

    /**
     * Indicates if payment was canceled by user.
     * Proxy to PaylineResult::isCanceled().
     *
     * @return bool
     */
    public function isPaymentCanceledByUser()
    {
        return $this->paylineResult->isCanceled();
    }

    /**
     * Indicates if transaction is a duplicate of an existing one.
     * Proxy to PaylineResult::isDuplicate().
     *
     * @return bool
     */
    public function isPaymentDuplicate()
    {
        return $this->paylineResult->isDuplicate();
    }

    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response;
    }

    /**
     * @return bool
     */
    public function hasResponse()
    {
        return isset($this->response);
    }
}
