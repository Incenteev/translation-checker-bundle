<?php

namespace Incenteev\TranslationCheckerBundle\Tests;

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
    public function testCompareCommand($locale, $valid)
    {
        self::bootKernel();

        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $application->setCatchExceptions(false);

        $input = new ArrayInput(array('command' => 'incenteev:translation:compare', 'locale' => $locale, '-d' => array('test')));
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
        return 'Incenteev\TranslationCheckerBundle\Tests\FixtureApp\TestKernel';
    }

    private static function deleteTmpDir()
    {
        if (!file_exists($dir = sys_get_temp_dir().'/incenteev_translation_checker')) {
            return;
        }

        $fs = new Filesystem();
        $fs->remove($dir);
    }
}
