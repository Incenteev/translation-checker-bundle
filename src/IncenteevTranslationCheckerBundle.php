<?php

namespace Incenteev\TranslationCheckerBundle;

use Incenteev\TranslationCheckerBundle\DependencyInjection\Compiler\ConfigureTranslatorPass;
use Incenteev\TranslationCheckerBundle\DependencyInjection\Compiler\ExtractorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * @final
 */
class IncenteevTranslationCheckerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigureTranslatorPass());
        $container->addCompilerPass(new ExtractorPass());
    }
}
