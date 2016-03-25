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

/**
 * Interface for web based payment gateways.
 */
interface WebGatewayInterface
{
    /**
     * Initiates the transaction on Payline servers.
     * Will trigger a "doWebPayment" SOAP call.
     *
     * $transaction will be updated with received payment session token.
     *
     * @param WebTransaction $transaction
     * @return PaylineResult
     */
    public function initiateWebTransaction(WebTransaction $transaction);

    /**
     * Checks if transaction identified by $paymentToken has been validated.
     * Will trigger a "getWebPaymentDetails".
     *
     * @param string $paymentToken
     * @return PaylineResult
     */
    public function verifyWebTransaction($paymentToken);
}
