<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Incenteev\TranslationCheckerBundle\Command\CompareCommand;
use Incenteev\TranslationCheckerBundle\Command\FindMissingCommand;
use Incenteev\TranslationCheckerBundle\Translator\Extractor\ChainExtractor;
use Incenteev\TranslationCheckerBundle\Translator\Extractor\JsExtractor;
use Incenteev\TranslationCheckerBundle\Translator\Extractor\SymfonyExtractor;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services->set('incenteev_translation_checker.exposing_translator')
        ->parent('translator.default')
        ->call('setFallbackLocales', [[]]);

    $services->alias('incenteev_translation_checker.extractor', 'incenteev_translation_checker.extractor.chain');

    $services->set('incenteev_translation_checker.extractor.chain', ChainExtractor::class)
        ->private()
        ->args([[]]);

    $services->set('incenteev_translation_checker.extractor.symfony', SymfonyExtractor::class)
        ->private()
        ->args([
            service('translation.extractor'),
            '%incenteev_translation_checker.extractor.symfony.paths%',
        ])
        ->tag('incenteev_translation_checker.extractor');

    $services->set('incenteev_translation_checker.extractor.js', JsExtractor::class)
        ->private()
        ->args([
            [],
            '',
        ])
        ->tag('incenteev_translation_checker.extractor');

    $services->set('incenteev_translation_checker.command.compare', CompareCommand::class)
        ->args([service('incenteev_translation_checker.exposing_translator')])
        ->tag('console.command', ['alias' => 'incenteev:translation:compare']);

    $services->set('incenteev_translation_checker.command.find_missing', FindMissingCommand::class)
        ->args([
            service('incenteev_translation_checker.exposing_translator'),
            service('incenteev_translation_checker.extractor'),
        ])
        ->tag('console.command', ['alias' => 'incenteev:translation:find-missing']);
};
