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

use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * This class represents a result received from Payline API.
 * It is constructed from the hash result returned by PaylineSDK.
 */
class PaylineResult
{
    const CODE_TRANSACTION_APPROVED = '00000';
    const CODE_TRANSACTION_INVALID_PREFIX = '023';
    const CODE_WALLET_ALREADY_EXISTS = '02502';
    const CODE_WALLET_NOT_SUPPORTED = '02511';
    const CODE_INTERNAL_ERROR = '02101';

    private $code;
    private $shortMessage;
    private $longMessage;

    /**
     * @var array
     */
    private $resultHash;

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    public function __construct($resultCode, $resultShortMessage, $resultLongMessage, array $resultHash = [])
    {
        $this->code = $resultCode;
        $this->shortMessage = $resultShortMessage;
        $this->longMessage = $resultLongMessage;
        $this->resultHash = $resultHash;
        $this->accessor = PropertyAccess::createPropertyAccessor();
    }

    public function isSuccessful()
    {
        return $this->code == static::CODE_TRANSACTION_APPROVED;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @return string
     */
    public function getShortMessage()
    {
        return $this->shortMessage;
    }

    /**
     * @return string
     */
    public function getLongMessage()
    {
        return $this->longMessage;
    }

    /**
     * Returns the result details hash.
     *
     * @return array
     */
    public function getResultHash()
    {
        return $this->resultHash;
    }

    /**
     * Returns a result item, identified by its $path, using property path notation.
     * Keep in mind that Payline result is stored as a hash.
     *
     * Example: When using Payline::verifyWebTransaction(), which uses getWebPaymentDetails from Payline API,
     * to get transaction.id item, you should do:
     *
     * ```php
     * $transactionId = $result->getItem('[transaction][id]');
     * ```
     *
     * Will return null if the item is not available.
     *
     * @param string $path Path to the detail in property path notation.
     * @return mixed|null
     */
    public function getItem($path)
    {
        if ($this->accessor->isReadable($this->resultHash, $path)) {
            return $this->accessor->getValue($this->resultHash, $path);
        }

        return null;
    }
}
