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

use Lolautruche\PaylineBundle\Payline\WebTransaction;
use Symfony\Component\EventDispatcher\Event;

class WebTransactionEvent extends Event
{
    /**
     * @var WebTransaction
     */
    private $transaction;

    public function __construct(WebTransaction $transaction)
    {
        $this->transaction = $transaction;
    }

    /**
     * @return WebTransaction
     */
    public function getTransaction()
    {
        return $this->transaction;
    }
}
