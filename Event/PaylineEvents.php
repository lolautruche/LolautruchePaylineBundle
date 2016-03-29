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

final class PaylineEvents
{
    const BEFORE_WEB_TRANSACTION_INITIATE = 'payline.before_web_transaction_initiate';
    const AFTER_WEB_TRANSACTION_INITIATE = 'payline.after_web_transaction_initiate';

    const BEFORE_WEB_TRANSACTION_VERIFY = 'payline.before_web_transaction_verify';
    const AFTER_WEB_TRANSACTION_VERIFY = 'payline.after_web_transaction_verify';
}
