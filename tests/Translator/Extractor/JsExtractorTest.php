<?php

namespace Incenteev\TranslationCheckerBundle\Tests\Translator\Extractor;

use Incenteev\TranslationCheckerBundle\Translator\Extractor\JsExtractor;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Translation\MessageCatalogue;

class JsExtractorTest extends TestCase
{
    /**
     * @dataProvider providePaths
     */
    public function testExtraction($path)
    {
        $extractor = new JsExtractor(array($path), 'js');
        $catalogue = new MessageCatalogue('en');

        $extractor->extract($catalogue);

        $expected = array(
            'js' => array(
                'test.single_quote',
                'test.double_quote',
                'choose.single_quote',
                'choose.double_quote',
                'test.single_quote_with_spaces',
                'test.double_quote_with_spaces',
                'choose.single_quote_with_spaces',
                'choose.double_quote_with_spaces',
                'test.with_domain', // Extracting explicit domain is not supported yet. We extract them in the default domain.
                // dynamic_key. is not exported as it is not the full key but only the first part of a dynamically-built key
            )
        );

        $this->assertEquals($expected, $catalogue->all(), '', 0, 10, true);// Order of translations is not relevant for the testing
    }

    public static function providePaths()
    {
        return array(
            'directory' => array(__DIR__.'/fixtures'),
            'file' => array(__DIR__.'/fixtures/test.js'),
        );
    }
}
