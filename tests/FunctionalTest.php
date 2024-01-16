<?php

namespace Incenteev\TranslationCheckerBundle\Tests;

use Incenteev\TranslationCheckerBundle\Tests\FixtureApp\TestKernel;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\Filesystem\Filesystem;

class FunctionalTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {
        self::deleteTmpDir();
    }

    public static function tearDownAfterClass(): void
    {
        self::deleteTmpDir();
    }

    /**
     * @dataProvider provideComparisonCases
     */
    public function testCompareCommand(string $locale, bool $valid)
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $input = new ArrayInput(array('command' => 'incenteev:translation:compare', 'locale' => $locale, '-d' => array('test')));
        $output = new NullOutput();

        $expectedExitCode = $valid ? 0 : 1;

        $this->assertSame($expectedExitCode, $application->run($input, $output));
    }

    /**
     * @dataProvider provideComparisonCases
     */
    public function testCompareCommandWithIcuTranslations(string $locale, bool $valid)
    {
        $kernel = self::bootKernel();

        $application = new Application($kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $input = new ArrayInput(array('command' => 'incenteev:translation:compare', 'locale' => $locale, '-d' => array('test_icu')));
        $output = new NullOutput();

        $expectedExitCode = $valid ? 0 : 1;

        $this->assertSame($expectedExitCode, $application->run($input, $output));
    }

    public static function provideComparisonCases(): iterable
    {
        return array(
            array('fr', true),
            array('de', false),
        );
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    private static function deleteTmpDir(): void
    {
        if (!file_exists($dir = sys_get_temp_dir().'/incenteev_translation_checker')) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }
}
