<?php

namespace Incenteev\TranslationCheckerBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @internal
 */
class IncenteevTranslationCheckerExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container): void
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->registerForAutoconfiguration('Incenteev\TranslationCheckerBundle\Translator\Extractor\ExtractorInterface')
            ->addTag('incenteev_translation_checker.extractor');

        $dirs = array();
        $overridePathPatterns = array();

        if ($container->hasParameter('kernel.root_dir')) {
            $overridePathPatterns[] = '%%kernel.root_dir%%/Resources/%s/views';
        }
        $overridePathPatterns[] = '%%kernel.project_dir%%/templates/bundles/%s';

        $registeredBundles = $container->getParameter('kernel.bundles');

        foreach ($config['extraction']['bundles'] as $bundle) {
            if (!isset($registeredBundles[$bundle])) {
                throw new \InvalidArgumentException(sprintf('The bundle %s is not registered in the kernel.', $bundle));
            }

            $reflection = new \ReflectionClass($registeredBundles[$bundle]);
            $dirs[] = dirname($reflection->getFilename()).'/Resources/views';

            foreach ($overridePathPatterns as $overridePath) {
                $dirs[] = sprintf($overridePath, $bundle);
            }
        }

        if ($container->hasParameter('kernel.root_dir')) {
            $dirs[] = '%kernel.root_dir%/Resources/views';
        }

        $dirs[] = '%kernel.project_dir%/templates';

        $container->setParameter('incenteev_translation_checker.extractor.symfony.paths', $dirs);

        if (empty($config['extraction']['js']['paths'])) {
            $container->removeDefinition('incenteev_translation_checker.extractor.js');
        } else {
            $container->getDefinition('incenteev_translation_checker.extractor.js')
                ->replaceArgument(0, $config['extraction']['js']['paths'])
                ->replaceArgument(1, $config['extraction']['js']['default_domain']);
        }
    }
}
