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

class ResultEvent extends Event
{
    /**
     * @var PaylineResult
     */
    private $result;

    public function __construct(PaylineResult $result)
    {
        $this->result = $result;
    }

    /**
     * @return PaylineResult
     */
    public function getResult()
    {
        return $this->result;
    }
}
