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
                        ->arrayNode('bundles')->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
