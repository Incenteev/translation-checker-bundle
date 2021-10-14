<?php

namespace Incenteev\TranslationCheckerBundle\Tests\Translator\Extractor;

use Incenteev\TranslationCheckerBundle\Translator\Extractor\SymfonyExtractor;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Translation\MessageCatalogue;

class SymfonyExtractorTest extends TestCase
{
    use ProphecyTrait;

    private $workingDir;

    public function testExtract()
    {
        $symfonyExtractor = $this->prophesize('Symfony\Component\Translation\Extractor\ExtractorInterface');

        $existingPaths = array(
            $this->workingDir.'/foo',
            $this->workingDir.'/bar',
        );

        foreach ($existingPaths as $dir) {
            mkdir($dir);
        }
        touch($this->workingDir.'/file');

        $nonDirPaths = array(
            $this->workingDir.'/missing',
            $this->workingDir.'/file',
        );

        $paths = array_merge($existingPaths, $nonDirPaths);

        $catalogue = new MessageCatalogue('en');

        $extractor = new SymfonyExtractor($symfonyExtractor->reveal(), $paths);

        $extractor->extract($catalogue);

        foreach ($existingPaths as $dir) {
            $symfonyExtractor->extract(Argument::exact($dir), Argument::exact($catalogue))->shouldHaveBeenCalled();
        }
        foreach ($nonDirPaths as $path) {
            $symfonyExtractor->extract(Argument::exact($path), Argument::exact($catalogue))->shouldNotHaveBeenCalled();
        }
    }

    protected function setup(): void
    {
        parent::setup();

        $this->workingDir = sys_get_temp_dir().'/translation_checker';

        if (is_dir($this->workingDir)) {
            $this->clean();
        } else {
            mkdir($this->workingDir);
        }
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clean();
        rmdir($this->workingDir);
    }

    private function clean()
    {
        foreach (glob($this->workingDir.'/*') as $file) {
            if (is_dir($file)) {
                @rmdir($file);
            } else {
                @unlink($file);
            }
        }
    }
}
