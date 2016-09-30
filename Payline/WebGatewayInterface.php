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
    const CODE_ACTION_DOREFUND = '421';
    
    /**
     * Initiates the transaction on Payline servers.
     * Will trigger a "doWebPayment" SOAP call.
     *
     * $transaction will be updated with received payment session token.
     *
     * @param WebTransaction $transaction
     *
     * @return PaylineResult
     */
    public function initiateWebTransaction(WebTransaction $transaction);

    /**
     * Checks if transaction identified by $paymentToken has been validated.
     * Will trigger a "getWebPaymentDetails".
     *
     * @param string $paymentToken
     *
     * @return PaylineResult
     */
    public function verifyWebTransaction($paymentToken);
    
    /**
     * Process a refund by calling first "getWebPaymentDetails"
     * Then "doRefund"
     *
     * @param $paymentToken
     * @param string $comment
     * @param int $sequenceNumber
     * @return PaylineResult
     */
    public function doRefund($paymentToken, $comment = '', $sequenceNumber = 0, $amount = null);
}
