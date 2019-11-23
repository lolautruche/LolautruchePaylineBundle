<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Tests\Event;

use Lolautruche\PaylineBundle\Event\ResultEvent;
use Lolautruche\PaylineBundle\Payline\PaylineResult;
use PHPUnit\Framework\TestCase;

class ResultEventTest extends TestCase
{
    public function testConstruct()
    {
        $result = new PaylineResult([]);
        $event = new ResultEvent($result);
        self::assertSame($result, $event->getResult());
    }
}
