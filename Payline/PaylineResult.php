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
     * Hash of data specific to the shop, that was passed to the transaction.
     *
     * @see WebTransaction::$privateData
     *
     * @var array
     */
    private $privateData = [];

    /**
     * @var \Symfony\Component\PropertyAccess\PropertyAccessor
     */
    private $accessor;

    public function __construct(array $resultHash)
    {
        $this->resultHash = $resultHash + [
            'result' => [
                'code' => null,
                'shortMessage' => null,
                'longMessage' => null
            ]
        ];
        $this->code = $this->resultHash['result']['code'];
        $this->shortMessage = $this->resultHash['result']['shortMessage'];
        $this->longMessage = $this->resultHash['result']['longMessage'];
        if (!empty($this->resultHash['privateDataList']['privateData'])) {
            foreach ($this->resultHash['privateDataList']['privateData'] as $data) {
                $this->privateData[$data['key']] = $data['value'];
            }
        }
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
     * You may omit array brackets if the item you want to get is directly accessible (i.e. not in a sub-array).
     *
     * Example 1:
     * When using Payline::verifyWebTransaction(), which uses getWebPaymentDetails from Payline API, to get transaction.id item, you should do:
     *
     * ```php
     * $transactionId = $result->getItem('[transaction][id]');
     * ```
     *
     * Example 2:
     * Get "redirectUrl" from the hash after calling Payline::initiateWebTransaction():
     *
     * ```php
     * $redirectUrl = $result->getItem('redirectUrl');
     * // Will have the same result as `$result->getItem('[redirectUrl]')`
     * ```
     *
     * Will return null if the item is not available.
     *
     * @param string $path Path to the detail in property path notation.
     * @return mixed|null
     */
    public function getItem($path)
    {
        // Property path doesn't contain array brackets, assume it is a direct access to an item.
        if (strpos($path, '[') === false && strpos($path, ']') === false) {
            $path = sprintf('[%s]', $path);
        }

        if ($this->accessor->isReadable($this->resultHash, $path)) {
            return $this->accessor->getValue($this->resultHash, $path);
        }

        return null;
    }

    /**
     * Returns a private data, identified by $key.
     * If no private data can be found for $key, will return $default.
     *
     * @param string $key
     * @param mixed $default The default value
     * @return mixed
     */
    public function getPrivateData($key, $default = null)
    {
        return isset($this->privateData[$key]) ? $this->privateData[$key] : $default;
    }

    /**
     * @return array
     */
    public function allPrivateData()
    {
        return $this->privateData;
    }
}
