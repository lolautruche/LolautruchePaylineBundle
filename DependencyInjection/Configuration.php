<?php

/*
 * This file is part of the LolautruchePaylineBundle package.
 *
 * (c) Jérôme Vieilledent <jerome@vieilledent.fr>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Lolautruche\PaylineBundle\DependencyInjection;

use Payline\PaylineSDK;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('lolautruche_payline');

        $rootNode
            ->children()
                ->scalarNode('merchant_id')->isRequired()->end()
                ->scalarNode('access_key')->isRequired()->end()
                ->scalarNode('contract_number')
                    ->isRequired()
                    ->info('Default contract number to use. Use quotes as it MUST be a string.')
                ->end()
                ->enumNode('default_currency')
                    ->info("Currency to use by default for transactions.\nYou may also pass a currency symbol. Accepted values are '€', '$', '£'")
                    ->values(['EUR', 'DOLLAR', 'CHF', 'POUND', 'CAD'])
                    ->isRequired()
                    ->beforeNormalization()
                        ->ifInArray(['€', '$', '£'])
                        ->then(function ($v) {
                            switch ($v) {
                                case '€':
                                    $v = 'EUR';
                                    break;
                                case '$':
                                    $v = 'DOLLAR';
                                    break;
                                case '£':
                                    $v = 'POUND';
                                    break;
                            }

                            return $v;
                        })
                    ->end()
                ->end()
                ->scalarNode('default_confirmation_route')
                    ->isRequired()
                    ->info('Default route name to use for confirmation page')
                    ->example(['default_confirmation_route' => 'my_confirmation_route'])
                ->end()
                ->scalarNode('default_error_route')
                    ->isRequired()
                    ->info('Default route name to use for error page (e.g. payment refused)')
                    ->example(['default_error_route' => 'my_error_route'])
                ->end()
                ->enumNode('environment')
                    ->isRequired()
                    ->values([
                        PaylineSDK::ENV_HOMO,
                        PaylineSDK::ENV_PROD,
                        PaylineSDK::ENV_INT,
                        PaylineSDK::ENV_DEV,
                    ])
                    ->beforeNormalization()
                        ->always(function ($v) { return strtoupper($v); })
                    ->end()
                    ->info("The payment environment\ne.g. 'homo' for 'Homologation', 'prod' for 'Production'")
                    ->defaultValue('HOMO')
                ->end()
                ->enumNode('log_level')
                    ->values(['debug', 'info', 'notice', 'warning', 'error', 'critical', 'alert', 'emergency'])
                    ->info('Log verbosity level for PaylineSDK.')
                    ->defaultValue('warning')
                ->end()
                ->arrayNode('proxy')
                    ->info('Proxy to use in order to reach Payline webservices')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('host')->defaultNull()->end()
                        ->integerNode('port')->defaultNull()->end()
                        ->scalarNode('login')->defaultNull()->end()
                        ->scalarNode('password')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
