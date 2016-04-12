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

use Lolautruche\PaylineBundle\Payline\PaylineResult;
use PHPUnit_Framework_TestCase;

class PaylineResultTest extends PHPUnit_Framework_TestCase
{
    public function testGetResultHash()
    {
        $hash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
            'foo' => 'bar',
            'transaction' => ['id' => '123xyz456'],
        ];
        $result = new PaylineResult($hash);
        self::assertSame($hash, $result->getResultHash());
    }

    public function testIsSuccessful()
    {
        // Successful result
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        self::assertTrue($result->isSuccessful());
        self::assertSame(PaylineResult::CODE_TRANSACTION_APPROVED, $result->getCode());

        // Unsuccessful result
        $result = new PaylineResult([
            'result' => [
                'code' => '12345',
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        self::assertFalse($result->isSuccessful());
        self::assertSame('12345', $result->getCode());
    }

    public function testIsCanceled()
    {
        // Canceled
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_CANCELED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        self::assertTrue($result->isCanceled());

        // Not canceled
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
        ]);
        self::assertFalse($result->isCanceled());
    }

    public function testIsDuplicate()
    {
        // Duplicate
        $shortMessage = 'foo';
        $longMessage = 'bar';
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_DUPLICATE,
                'shortMessage' => $shortMessage,
                'longMessage' => $longMessage,
            ],
        ]);
        self::assertTrue($result->isDuplicate());
        self::assertSame($shortMessage, $result->getShortMessage());
        self::assertSame($longMessage, $result->getLongMessage());

        // Not canceled
        $shortMessage = 'foo2';
        $longMessage = 'bar2';
        $result = new PaylineResult([
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => $shortMessage,
                'longMessage' => $longMessage,
            ],
        ]);
        self::assertFalse($result->isDuplicate());
        self::assertSame($shortMessage, $result->getShortMessage());
        self::assertSame($longMessage, $result->getLongMessage());
    }

    /**
     * @dataProvider getItemProvider
     */
    public function testGetItem(array $resultHash, $path, $expected)
    {
        $result = new PaylineResult($resultHash);
        self::assertSame($expected, $result->getItem($path));
    }

    public function getItemProvider()
    {
        return [
            [['foo' => 'bar'], 'fooz', null],
            [['foo' => 'bar'], 'foo', 'bar'],
            [['foo' => 'bar'], '[foo]', 'bar'],
            [
                [
                    'foo' => 'bar',
                    'transaction' => ['id' => '123xyz456'],
                ],
                '[transaction][id]',
                '123xyz456',
            ],
            [
                [
                    'foo' => [
                        'bar' => [
                            'baz' => 12345,
                        ],
                    ],
                ],
                'foo',
                [
                    'bar' => [
                        'baz' => 12345,
                    ],
                ]
            ],
            [
                [
                    'foo' => [
                        'bar' => [
                            'baz' => 12345,
                        ],
                    ],
                ],
                '[foo][bar]',
                [
                    'baz' => 12345,
                ]
            ],
            [
                [
                    'foo' => [
                        'bar' => [
                            'baz' => 12345,
                        ],
                    ],
                ],
                '[foo][bar][baz]',
                12345,
            ],
        ];
    }

    public function testGetAllPrivateData()
    {
        $hash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
            'privateDataList' => [
                'privateData' => []
            ]
        ];
        $expectedPrivateData = [
            'foo' => 'bar',
            'baz' => '123456',
        ];
        foreach ($expectedPrivateData as $key => $value) {
            $hash['privateDataList']['privateData'][] = ['key' => $key, 'value' => $value];
        }

        $result = new PaylineResult($hash);
        self::assertSame($expectedPrivateData, $result->allPrivateData());
    }

    /**
     * @dataProvider getPrivateDataProvider
     */
    public function testGetPrivateData(array $privateDataList, $key, $defaultValue, $expectedValue)
    {
        $hash = [
            'result' => [
                'code' => PaylineResult::CODE_TRANSACTION_APPROVED,
                'shortMessage' => 'foo',
                'longMessage' => 'bar',
            ],
            'privateDataList' => [
                'privateData' => $privateDataList,
            ]
        ];

        $result = new PaylineResult($hash);
        self::assertNull($result->getPrivateData('nonexistentkey'));
        self::assertSame($expectedValue, $result->getPrivateData($key, $defaultValue));
    }

    public function getPrivateDataProvider()
    {
        return [
            [
                [['key' => 'foo', 'value' => 'bar']],
                'foo',
                null,
                'bar',
            ],
            [
                [['key' => 'foo', 'value' => 'bar']],
                'biz',
                'default_value',
                'default_value',
            ],
            [
                [
                    ['key' => 'foo', 'value' => 'bar'],
                    ['key' => 'baz', 'value' => '123456'],
                ],
                'baz',
                'bar',
                '123456',
            ],
        ];
    }
}
