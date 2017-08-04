<?php

namespace Incenteev\TranslationCheckerBundle\Tests\DependencyInjection\Compiler;

use Incenteev\TranslationCheckerBundle\DependencyInjection\Compiler\ExtractorPass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class ExtractorPassTest extends TestCase
{
    public function testRegisterExtractors()
    {
        $container = $this->createContainerBuilder();

        $container->register('foo', 'stdClass')
            ->addTag('incenteev_translation_checker.extractor');

        $container->register('bar', 'stdClass')
            ->addTag('incenteev_translation_checker.extractor');

        $pass = new ExtractorPass();

        $pass->process($container);

        $def = $container->getDefinition('incenteev_translation_checker.extractor.chain');

        $this->assertEquals(array(new Reference('foo'), new Reference('bar')), $def->getArgument(0));
    }

    public function testRegisterNoExtractors()
    {
        $container = $this->createContainerBuilder();

        $pass = new ExtractorPass();

        $pass->process($container);

        $def = $container->getDefinition('incenteev_translation_checker.extractor.chain');

        $this->assertEquals(array(), $def->getArgument(0));
    }

    public function testRegisterSingleExtractor()
    {
        $container = $this->createContainerBuilder();

        $container->register('foo', 'stdClass')
            ->addTag('incenteev_translation_checker.extractor');

        $pass = new ExtractorPass();

        $pass->process($container);

        $alias = $container->getAlias('incenteev_translation_checker.extractor');

        $this->assertEquals('foo', (string) $alias);
        $this->assertFalse($alias->isPublic());
    }

    public function testRegisterSingleExtractorPreservesVisibility()
    {
        $container = $this->createContainerBuilder();

        $container->register('foo', 'stdClass')
            ->addTag('incenteev_translation_checker.extractor');
        $container->getAlias('incenteev_translation_checker.extractor')->setPublic(true);

        $pass = new ExtractorPass();

        $pass->process($container);

        $alias = $container->getAlias('incenteev_translation_checker.extractor');

        $this->assertEquals('foo', (string) $alias);
        $this->assertTrue($alias->isPublic());
    }

    private function createContainerBuilder()
    {
        $container = new ContainerBuilder();
        $container->register('incenteev_translation_checker.extractor.chain', 'Incenteev\TranslationCheckerBundle\Translator\Extractor\ChainExtractor')
            ->addArgument(array());
        $container->setAlias('incenteev_translation_checker.extractor', new Alias('incenteev_translation_checker.extractor.chain', false));

        return $container;
    }
}
