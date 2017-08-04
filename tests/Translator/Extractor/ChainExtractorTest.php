<?php

namespace Incenteev\TranslationCheckerBundle\Tests\Translator\Extractor;

use Incenteev\TranslationCheckerBundle\Translator\Extractor\ChainExtractor;
use PHPUnit\Framework\TestCase;

class ChainExtractorTest extends TestCase
{
    public function testExtract()
    {
        $extractor1 = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\Extractor\ExtractorInterface');
        $extractor2 = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\Extractor\ExtractorInterface');

        $catalogue = $this->prophesize('Symfony\Component\Translation\MessageCatalogue');

        $extractor1->extract($catalogue)->shouldBeCalled();
        $extractor2->extract($catalogue)->shouldBeCalled();

        $extractor = new ChainExtractor(array($extractor1->reveal(), $extractor2->reveal()));

        $extractor->extract($catalogue->reveal());
    }
}
