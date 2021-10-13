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

    protected function setUp(): void
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

        $this->assertParameter(array('%kernel.project_dir%/templates'), 'incenteev_translation_checker.extractor.symfony.paths');
        $this->assertFalse($this->containerBuilder->hasDefinition('incenteev_translation_checker.extractor.js'));
    }

    public function testDefaultPathsWithRootDir()
    {
        $this->containerBuilder->setParameter('kernel.root_dir', __DIR__);

        $extension = new IncenteevTranslationCheckerExtension();

        $extension->load(array(), $this->containerBuilder);

        $this->assertParameter(array(
            '%kernel.root_dir%/Resources/views',
            '%kernel.project_dir%/templates',
        ), 'incenteev_translation_checker.extractor.symfony.paths');
        $this->assertFalse($this->containerBuilder->hasDefinition('incenteev_translation_checker.extractor.js'));
    }

    public function testBundlePaths()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('bundles' => array('IncenteevTranslationCheckerBundle')));

        $extension->load(array($config), $this->containerBuilder);

        $expectedPaths = array(
            dirname(dirname(__DIR__)).'/src/Resources/views',
            '%kernel.project_dir%/templates/bundles/IncenteevTranslationCheckerBundle',
            '%kernel.project_dir%/templates',
        );

        $this->assertParameter($expectedPaths, 'incenteev_translation_checker.extractor.symfony.paths');
    }

    public function testBundlePathsWithRootDir()
    {
        $this->containerBuilder->setParameter('kernel.root_dir', __DIR__);

        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('bundles' => array('IncenteevTranslationCheckerBundle')));

        $extension->load(array($config), $this->containerBuilder);

        $expectedPaths = array(
            dirname(dirname(__DIR__)).'/src/Resources/views',
            '%kernel.root_dir%/Resources/IncenteevTranslationCheckerBundle/views',
            '%kernel.project_dir%/templates/bundles/IncenteevTranslationCheckerBundle',
            '%kernel.root_dir%/Resources/views',
            '%kernel.project_dir%/templates',
        );

        $this->assertParameter($expectedPaths, 'incenteev_translation_checker.extractor.symfony.paths');
    }

    public function testNotRegisteredBundle()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('bundles' => array('TwigBundle')));

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('The bundle TwigBundle is not registered in the kernel.');

        $extension->load(array($config), $this->containerBuilder);
    }

    public function testJsExtractionPaths()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('js' => array('paths' => array('%kernel.project_dir%/web/js'))));

        $extension->load(array($config), $this->containerBuilder);

        $this->assertTrue($this->containerBuilder->hasDefinition('incenteev_translation_checker.extractor.js'));

        $def = $this->containerBuilder->getDefinition('incenteev_translation_checker.extractor.js');

        $this->assertEquals(array('%kernel.project_dir%/web/js'), $def->getArgument(0));
        $this->assertEquals('messages', $def->getArgument(1));
    }

    public function testJsExtractionDomain()
    {
        $extension = new IncenteevTranslationCheckerExtension();

        $config = array('extraction' => array('js' => array('paths' => array('%kernel.project_dir%/web/js'), 'default_domain' => 'js_messages')));

        $extension->load(array($config), $this->containerBuilder);

        $this->assertTrue($this->containerBuilder->hasDefinition('incenteev_translation_checker.extractor.js'));

        $def = $this->containerBuilder->getDefinition('incenteev_translation_checker.extractor.js');

        $this->assertEquals(array('%kernel.project_dir%/web/js'), $def->getArgument(0));
        $this->assertEquals('js_messages', $def->getArgument(1));
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
