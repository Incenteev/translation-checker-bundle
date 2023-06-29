<?php

namespace Incenteev\TranslationCheckerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @internal
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('incenteev_translation_checker');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
                ->arrayNode('extraction')
                    ->addDefaultsIfNotSet()
                    ->fixXmlConfig('bundle')
                    ->children()
                        ->arrayNode('bundles')
                            ->prototype('scalar')->end()
                        ->end()
                        ->arrayNode('js')
                            ->addDefaultsIfNotSet()
                            ->fixXmlConfig('path')
                            ->children()
                                ->scalarNode('default_domain')
                                    ->info('The default domain used for JS translation (should match the JsTranslationBundle config)')
                                    ->defaultValue('messages')
                                ->end()
                                ->arrayNode('paths')
                                    ->prototype('scalar')->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
