<?php

namespace Incenteev\TranslationCheckerBundle\Tests\FixtureApp;

use Incenteev\TranslationCheckerBundle\IncenteevTranslationCheckerBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class TestKernel extends Kernel
{
    public function registerBundles()
    {
        return array(
            new FrameworkBundle(),
            new IncenteevTranslationCheckerBundle(),
        );
    }

    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $loader->load(function (ContainerBuilder $container) {
            $container->loadFromExtension('framework', array(
                'translator' => array('fallback' => 'en'),
                'secret' => 'test',
            ));
            // Register a NullLogger to avoid getting the stderr default logger of FrameworkBundle
            $container->register('logger', 'Psr\Log\NullLogger');
        });
    }

    public function getProjectDir()
    {
        // Fake implementation so that the old root_dir/Resources/translations and the new project_dir/translations both
        // map to the same folder in our fixture app to avoid getting a deprecation warning when running tests with 4.2+
        // but keeping compat with running tests on 3.4.
        return __DIR__.'/Resources';
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir().'/incenteev_translation_checker';
    }

    public function getLogDir()
    {
        return $this->getCacheDir();
    }
}
