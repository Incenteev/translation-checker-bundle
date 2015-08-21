<?php

namespace Incenteev\TranslationCheckerBundle;

use Incenteev\TranslationCheckerBundle\DependencyInjection\Compiler\ConfigureTranslatorPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class IncenteevTranslationCheckerBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new ConfigureTranslatorPass());
    }
}
