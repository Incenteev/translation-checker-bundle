<?php

namespace Incenteev\TranslationCheckerBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @internal
 */
final class ExtractorPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $extractors = array();

        foreach ($container->findTaggedServiceIds('incenteev_translation_checker.extractor') as $id => $tags) {
            $extractors[] = new Reference($id);
        }

        // If there is only one configured extractor, skip the chain one
        if (1 === count($extractors)) {
            $container->setAlias('incenteev_translation_checker.extractor', new Alias((string) $extractors[0], $container->getAlias('incenteev_translation_checker.extractor')->isPublic()));

            return;
        }

        $container->getDefinition('incenteev_translation_checker.extractor.chain')->replaceArgument(0, $extractors);
    }
}
