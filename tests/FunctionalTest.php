<?php

namespace Incenteev\TranslationCheckerBundle\Tests;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

class FunctionalTest extends KernelTestCase
{
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

    public static function provideComparisonCases()
    {
        return array(
            array('fr', true),
            array('de', false),
        );
    }

    protected static function getKernelClass()
    {
        return 'Incenteev\TranslationCheckerBundle\Tests\FixtureApp\TestKernel';
    }
}
