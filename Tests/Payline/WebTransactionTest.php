<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Tests\Payline;

use DateTime;
use Lolautruche\PaylineBundle\Payline\WebTransaction;
use PHPUnit\Framework\TestCase;

class WebTransactionTest extends TestCase
{
    public function testConstructor()
    {
        $amount = 11900;
        $orderRef = '123xyz456';
        $date = new DateTime();
        $transaction = new WebTransaction($amount, $orderRef, $date);
        self::assertSame($amount, $transaction->getAmount());
        self::assertSame($orderRef, $transaction->getOrderRef());
        self::assertSame($date, $transaction->getOrderDate());
    }

    /**
     * @dataProvider addExtraOptionProvider
     */
    public function testAddExtraOption($optionPath, $value, array $expectedOptions)
    {
        $transaction = new WebTransaction(100, 'foo', new DateTime());
        self::assertEmpty($transaction->getExtraOptions());
        $transaction->addExtraOption($optionPath, $value);
        self::assertSame($expectedOptions, $transaction->getExtraOptions());
    }

    public function addExtraOptionProvider()
    {
        return [
            ['[buyer][email]', 'foo@bar.com', ['buyer' => ['email' => 'foo@bar.com']]],
            [
                '[foo][bar][baz]',
                ['some', 'thing'],
                [
                    'foo' => [
                        'bar' => [
                            'baz' => ['some', 'thing'],
                        ],
                    ],
                ],
            ],
            ['some_key', 123, ['some_key' => 123]],
        ];
    }

    public function testAddPrivateData()
    {
        $transaction = new WebTransaction(100, 'foo', new DateTime());
        self::assertEmpty($transaction->getPrivateData());

        $expectedPrivateData = [
            'fooBar' => '123xyz789',
            'bazBiz' => 'hôhô',
        ];
        $transaction->addPrivateData('fooBar', $expectedPrivateData['fooBar']);
        $transaction->addPrivateData('bazBiz', $expectedPrivateData['bazBiz']);
        self::assertSame($expectedPrivateData, $transaction->getPrivateData());
    }
}
