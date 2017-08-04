<?php

namespace Incenteev\TranslationCheckerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $treeBuilder->root('incenteev_translation_checker')
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
