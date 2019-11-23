<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\Tests\DependencyInjection;

use Lolautruche\PaylineBundle\DependencyInjection\Configuration;
use Matthias\SymfonyConfigTest\PhpUnit\ConfigurationTestCaseTrait;
use Payline\PaylineSDK;
use PHPUnit\Framework\TestCase;

class ConfigurationTest extends TestCase
{
    use ConfigurationTestCaseTrait;

    /**
     * Return the instance of ConfigurationInterface that should be used by the
     * Configuration-specific assertions in this test-case
     *
     * @return \Symfony\Component\Config\Definition\ConfigurationInterface
     */
    protected function getConfiguration()
    {
        return new Configuration();
    }

    /**
     * @dataProvider currencySymbolProvider
     */
    public function testCurrencySymbols($currencySymbol, $expectedProcessedValue)
    {
        $minimalConfig = [
            'merchant_id' => '123',
            'access_key' => '456xyz789',
            'contract_number' => '1234567',
            'default_confirmation_route' => 'confirmation',
            'default_error_route' => 'error',
            'environment' => PaylineSDK::ENV_HOMO,
        ];

        $this->assertProcessedConfigurationEquals(
            [['default_currency' => $currencySymbol] + $minimalConfig],
            [
                'default_currency' => $expectedProcessedValue,
                'log_level' => 'warning',
                'proxy' => [
                    'host' => null,
                    'port' => null,
                    'login' => null,
                    'password' => null,
                ],
            ] + $minimalConfig
        );
    }

    public function currencySymbolProvider()
    {
        return [
            ['€', 'EUR'],
            ['$', 'DOLLAR'],
            ['£', 'POUND'],
        ];
    }

    /**
     * @dataProvider lowercaseEnvironmentProvider
     */
    public function testLowercaseEnvironment($lowerCaseEnv, $expectedEnv)
    {
        $minimalConfig = [
            'merchant_id' => '123',
            'access_key' => '456xyz789',
            'contract_number' => '1234567',
            'default_confirmation_route' => 'confirmation',
            'default_error_route' => 'error',
            'default_currency' => 'EUR',
        ];

        $this->assertProcessedConfigurationEquals(
            [['environment' => $lowerCaseEnv] + $minimalConfig],
            [
                'environment' => $expectedEnv,
                'log_level' => 'warning',
                'proxy' => [
                    'host' => null,
                    'port' => null,
                    'login' => null,
                    'password' => null,
                ],
            ] + $minimalConfig
        );
    }

    public function lowercaseEnvironmentProvider()
    {
        return [
            [strtolower(PaylineSDK::ENV_HOMO), PaylineSDK::ENV_HOMO],
            [strtolower(PaylineSDK::ENV_DEV), PaylineSDK::ENV_DEV],
            [strtolower(PaylineSDK::ENV_INT), PaylineSDK::ENV_INT],
            [strtolower(PaylineSDK::ENV_PROD), PaylineSDK::ENV_PROD],
        ];
    }
}
