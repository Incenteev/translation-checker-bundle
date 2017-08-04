<?php

namespace Incenteev\TranslationCheckerBundle\Tests\DependencyInjection;

use Incenteev\TranslationCheckerBundle\DependencyInjection\IncenteevTranslationCheckerExtension;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class IncenteevTranslationCheckerExtensionTest extends TestCase
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    protected function setUp()
    {
        $this->containerBuilder = new ContainerBuilder();
        $this->containerBuilder->setParameter('kernel.bundles', array(
            'IncenteevTranslationCheckerBundle' => 'Incenteev\TranslationCheckerBundle\IncenteevTranslationCheckerBundle',
            'FrameworkBundle' => 'Symfony\Bundle\FrameworkBundle\FrameworkBundle',
        ));
    }

    public function testDefaultPaths()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $extension->load(array(), $this->containerBuilder);

        $this->assertParameter(array('%kernel.root_dir%/Resources/views'), 'incenteev_translation_checker.extractor.symfony.paths');
    }

    public function testBundlePaths()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('bundles' => array('IncenteevTranslationCheckerBundle')));

        $extension->load(array($config), $this->containerBuilder);

        $expectedPaths = array(
            dirname(dirname(__DIR__)).'/src/Resources/views',
            '%kernel.root_dir%/Resources/IncenteevTranslationCheckerBundle/views',
            '%kernel.root_dir%/Resources/views',
        );

        $this->assertParameter($expectedPaths, 'incenteev_translation_checker.extractor.symfony.paths');
    }

    /**
     * @expectedException \InvalidArgumentException
     * @expectedExceptionMessage The bundle TwigBundle is not registered in the kernel.
     */
    public function testNotRegisteredBundle()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('bundles' => array('TwigBundle')));

        $extension->load(array($config), $this->containerBuilder);
    }

    /**
     * @param mixed  $value
     * @param string $key
     */
    private function assertParameter($value, $key)
    {
        $this->assertEquals($value, $this->containerBuilder->getParameter($key), sprintf('%s parameter is correct', $key));
    }
}
