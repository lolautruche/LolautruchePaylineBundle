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

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PaylineController extends Controller
{
    public function paymentNotificationAction(Request $request)
    {
        $payline = $this->get('lolautruche_payline.gateway');
        $result = $payline->verifyWebTransaction($request->get('token'));

        if (!$result->isSuccessful()) {
            return new Response('KO');
        }

        return new Response('OK');
    }

    public function backToShopAction(Request $request)
    {
        dump($request);
        return new Response('OK');
    }
}
