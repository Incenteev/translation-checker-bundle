<?php

namespace Incenteev\TranslationCheckerBundle\Tests\Command;

use Incenteev\TranslationCheckerBundle\Command\CompareCommand;
use Prophecy\PhpUnit\ProphecyTestCase;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\MessageCatalogue;

class CompareCommandTest extends ProphecyTestCase
{
    /**
     * @dataProvider provideCommandData
     */
    public function testExecute($sourceLocale, array $sourceMessages, $comparedLocale, array $comparedMessages, array $input, $expectedExitCode, $expectedMessages, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $container = $this->prophesize('Symfony\Component\DependencyInjection\ContainerInterface');
        $container->get('incenteev_translation_checker.exposing_translator')->willReturn($loader);

        $loader->getCatalogue($sourceLocale)->willReturn(new MessageCatalogue($sourceLocale, $sourceMessages));
        $loader->getCatalogue($comparedLocale)->willReturn(new MessageCatalogue($comparedLocale, $comparedMessages));

        $command = new CompareCommand();
        $command->setContainer($container->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute($input, array('decorated' => false, 'verbosity' => $verbosity));

        $this->assertEquals($expectedExitCode, $exitCode);

        foreach ((array) $expectedMessages as $message) {
            $this->assertContains($message, $tester->getDisplay());
        }
    }

    public function provideCommandData()
    {
        return array(
            'sync with en' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                'fr',
                array('messages' => array('foo' => 'baz')),
                array('locale' => 'fr'),
                0,
                'The fr catalogue is in sync with the en one.',
            ),
            'sync with en explicit' => array(
                'en',
                array('messages' => array('foo' => 'bar'), 'test' => array('me' => 'Me')),
                'fr',
                array('messages' => array('foo' => 'baz'), 'test' => array('me' => 'Moi')),
                array('locale' => 'fr', 'source' => 'en'),
                0,
                'The fr catalogue is in sync with the en one.',
                OutputInterface::VERBOSITY_VERBOSE,
            ),
            'missing message' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                'fr',
                array('messages' => array()),
                array('locale' => 'fr'),
                1,
                '1 messages are missing in the messages domain',
            ),
            'missing message verbose' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                'fr',
                array('messages' => array()),
                array('locale' => 'fr'),
                1,
                array('1 messages are missing in the messages domain', '    foo'),
                OutputInterface::VERBOSITY_VERBOSE,
            ),
            'obsolete message' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                'fr',
                array('messages' => array('foo' => 'bar', 'bar' => 'baz', 'old' => 'one')),
                array('locale' => 'fr'),
                1,
                '2 messages are obsolete in the messages domain',
            ),
            'obsolete message verbose' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                'fr',
                array('messages' => array('foo' => 'bar', 'bar' => 'baz', 'old.key' => 'one')),
                array('locale' => 'fr'),
                1,
                array('2 messages are obsolete in the messages domain', '    bar', '    old.key'),
                OutputInterface::VERBOSITY_VERBOSE,
            ),
            'missing and obsolete message' => array(
                'en',
                array('messages' => array('foo' => 'bar'), 'test' => array('hello' => 'world')),
                'fr',
                array('messages' => array('foo' => 'bar', 'bar' => 'baz', 'old' => 'one')),
                array('locale' => 'fr'),
                1,
                array('2 messages are obsolete in the messages domain', '1 messages are missing in the test domain'),
            ),
            'domain restriction sync' => array(
                'en',
                array('messages' => array('foo' => 'bar'), 'test' => array('foo' => 'bar')),
                'fr',
                array('messages' => array('foo' => 'baz')),
                array('locale' => 'fr', '--domain' => array('messages', 'other')),
                0,
                array('The fr catalogue is in sync with the en one.', 'Checking the domains messages'),
            ),
            'domain restriction missing' => array(
                'en',
                array('messages' => array('foo' => 'bar'), 'test' => array('foo' => 'bar')),
                'fr',
                array('messages' => array('foo' => 'baz'), 'other' => array('hello' => 'world')),
                array('locale' => 'fr', '--domain' => array('test', 'other')),
                1,
                array('1 messages are missing in the test domain', 'Checking the domains other, test'),
            ),
        );
    }
}
