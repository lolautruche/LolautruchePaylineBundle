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

use Lolautruche\PaylineBundle\DependencyInjection\LolautruchePaylineExtension;
use Matthias\SymfonyDependencyInjectionTest\PhpUnit\AbstractExtensionTestCase;
use Payline\PaylineSDK;
use Symfony\Component\DependencyInjection\Extension\ExtensionInterface;

class LolautruchePaylineExtensionTest extends AbstractExtensionTestCase
{
    /**
     * Return an array of container extensions you need to be registered for each test (usually just the container
     * extension you are testing.
     *
     * @return ExtensionInterface[]
     */
    protected function getContainerExtensions(): array
    {
        return [new LolautruchePaylineExtension()];
    }

    /**
     * @dataProvider basicConfigProvider
     */
    public function testBasicConfig($currency, $logLevel = null)
    {
        $merchantId = '123';
        $accessKey = '456xyz789';
        $contractNumber = '1234567';
        $environment = PaylineSDK::ENV_HOMO;
        $confirmationRoute = 'confirmation';
        $errorRoute = 'error';
        $logLevel = $logLevel ?: 'warning';
        $config = [
            'merchant_id' => $merchantId,
            'access_key' => $accessKey,
            'contract_number' => $contractNumber,
            'environment' => $environment,
            'default_confirmation_route' => $confirmationRoute,
            'default_error_route' => $errorRoute,
            'default_currency' => $currency,
            'log_level' => $logLevel,
        ];

        $this->load($config);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.merchant_id', $merchantId);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.access_key', $accessKey);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.default_contract_number', $contractNumber);
        $this->assertContainerBuilderHasParameter(
            'lolautruche_payline.default_currency',
            constant('Lolautruche\PaylineBundle\Payline\WebTransaction::CURRENCY_'.$currency)
        );
        $this->assertContainerBuilderHasParameter('lolautruche_payline.environment', $environment);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.log_verbosity', constant('Monolog\Logger::'.strtoupper($logLevel)));
        $this->assertContainerBuilderHasParameter('lolautruche_payline.default_confirmation_route', $confirmationRoute);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.default_error_route', $errorRoute);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.proxy.host', null);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.proxy.port', null);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.proxy.login', null);
        $this->assertContainerBuilderHasParameter('lolautruche_payline.proxy.password', null);
    }

    public function basicConfigProvider()
    {
        return [
            ['EUR', null],
            ['EUR', 'info'],
            ['DOLLAR', 'debug'],
            ['CAD', 'notice'],
            ['CHF', 'error'],
            ['POUND', 'emergency'],
        ];
    }
}
