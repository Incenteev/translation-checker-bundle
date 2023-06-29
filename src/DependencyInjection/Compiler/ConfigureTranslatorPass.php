<?php

namespace Incenteev\TranslationCheckerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class ConfigureTranslatorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('incenteev_translation_checker.exposing_translator')) {
            return;
        }

        if (!$container->has('translator.default')) {
            return;
        }

        $translatorDef = $container->findDefinition('translator.default');

        $optionsArgumentIndex = 4;

        $options = $translatorDef->getArgument($optionsArgumentIndex);

        if (!is_array($options)) {
            // Weird setup. Reset all options
            $options = array();
        }

        // use a separate cache as we have no fallback locales
        $options['cache_dir'] = '%kernel.cache_dir%/incenteev_translations';

        $container->findDefinition('incenteev_translation_checker.exposing_translator')
            ->replaceArgument($optionsArgumentIndex, $options);
    }
}
