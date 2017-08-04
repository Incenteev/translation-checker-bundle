<?php

namespace Incenteev\TranslationCheckerBundle\DependencyInjection;

use Symfony\Component\HttpKernel\DependencyInjection\ConfigurableExtension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IncenteevTranslationCheckerExtension extends ConfigurableExtension
{
    public function loadInternal(array $config, ContainerBuilder $container)
    {
        $loader = new XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        // supports autoconfiguration of extractors in Symfony 3.3+
        if (method_exists($container, 'registerForAutoconfiguration')) {
            $container->registerForAutoconfiguration('Incenteev\TranslationCheckerBundle\Translator\Extractor\ExtractorInterface')
                ->addTag('incenteev_translation_checker.extractor');
        }

        $dirs = array();
        $overridePath = '%%kernel.root_dir%%/Resources/%s/views';
        $registeredBundles = $container->getParameter('kernel.bundles');

        foreach ($config['extraction']['bundles'] as $bundle) {
            if (!isset($registeredBundles[$bundle])) {
                throw new \InvalidArgumentException(sprintf('The bundle %s is not registered in the kernel.', $bundle));
            }

            $reflection = new \ReflectionClass($registeredBundles[$bundle]);
            $dirs[] = dirname($reflection->getFilename()).'/Resources/views';
            $dirs[] = sprintf($overridePath, $bundle);
        }
        $dirs[] = '%kernel.root_dir%/Resources/views';

        $container->setParameter('incenteev_translation_checker.extractor.symfony.paths', $dirs);
    }
}
