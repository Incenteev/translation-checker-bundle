<?php

namespace Incenteev\TranslationCheckerBundle\Tests\Command;

use Incenteev\TranslationCheckerBundle\Command\CompareCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\MessageCatalogue;

class CompareCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @dataProvider provideCommandData
     */
    public function testExecute($sourceLocale, array $sourceMessages, $comparedLocale, array $comparedMessages, array $input, $expectedExitCode, $expectedMessages, $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $loader->getCatalogue($sourceLocale)->willReturn(new MessageCatalogue($sourceLocale, $sourceMessages));
        $loader->getCatalogue($comparedLocale)->willReturn(new MessageCatalogue($comparedLocale, $comparedMessages));

        $command = new CompareCommand($loader->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute($input, array('decorated' => false, 'verbosity' => $verbosity));

        $this->assertEquals($expectedExitCode, $exitCode);

        foreach ((array) $expectedMessages as $message) {
            $this->assertStringContainsString($message, $tester->getDisplay());
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
            'missing and obsolete message with obsolete only' => array(
                'en',
                array('messages' => array('foo' => 'bar'), 'test' => array('hello' => 'world')),
                'fr',
                array('messages' => array('foo' => 'bar', 'bar' => 'baz', 'old' => 'one')),
                array('locale' => 'fr', '--obsolete-only' => true),
                1,
                array('2 messages are obsolete in the messages domain'),
            ),
            'missing message with obsolete only' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                'fr',
                array('messages' => array()),
                array('locale' => 'fr', '--obsolete-only' => true),
                0,
                'The fr catalogue is in sync with the en one.',
            ),
        );
    }

    public function testFailsForNonExistentWhitelist()
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $loader->getCatalogue('en')->willReturn(new MessageCatalogue('en', array('messages' => array('foo' => 'bar'))));
        $loader->getCatalogue('fr')->willReturn(new MessageCatalogue('fr', array('messages' => array('foo' => 'baz'))));

        $command = new CompareCommand($loader->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('locale' => 'fr', '--whitelist-file' => __DIR__.'/../fixtures/non_existent.yml'), array('decorated' => false));

        $this->assertEquals(1, $exitCode);

        $this->assertStringMatchesFormat('%AThe whitelist file "%s" does not exist.%A', $tester->getDisplay());
    }

    public function testFailsForInvalidWhitelist()
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $loader->getCatalogue('en')->willReturn(new MessageCatalogue('en', array('messages' => array('foo' => 'bar'))));
        $loader->getCatalogue('fr')->willReturn(new MessageCatalogue('fr', array('messages' => array('foo' => 'baz'))));

        $command = new CompareCommand($loader->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('locale' => 'fr', '--whitelist-file' => __DIR__.'/../fixtures/invalid_whitelist.yml'), array('decorated' => false));

        $this->assertEquals(1, $exitCode);

        $this->assertStringMatchesFormat('%AThe whitelist file "%s" is invalid. It must be a Yaml file containing a map.%A', $tester->getDisplay());
    }

    public function testSucceedWithWhitelistedMessages()
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $loader->getCatalogue('en')->willReturn(new MessageCatalogue('en', array('incenteev_tests' => array('foo' => 'bar', 'this key can go missing' => 'not defined in fr'))));
        $loader->getCatalogue('fr')->willReturn(new MessageCatalogue('fr', array('incenteev_tests' => array('foo' => 'baz', 'this.one.also' => 'obsolete... or no'))));

        $command = new CompareCommand($loader->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('locale' => 'fr', '--whitelist-file' => __DIR__.'/../fixtures/whitelist.yml'), array('decorated' => false));

        $this->assertEquals(0, $exitCode);
    }

    public function testFailsWithWhitelistedMessagesAndMissingMessage()
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $loader->getCatalogue('en')->willReturn(new MessageCatalogue('en', array('incenteev_tests' => array(
            'foo' => 'bar',
            'this key can go missing' => 'not defined in fr',
            'this key is required' => 'but missing in fr',
        ))));
        $loader->getCatalogue('fr')->willReturn(new MessageCatalogue('fr', array('incenteev_tests' => array('foo' => 'baz', 'this.one.also' => 'obsolete... or no'))));

        $command = new CompareCommand($loader->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('locale' => 'fr', '--whitelist-file' => __DIR__.'/../fixtures/whitelist.yml'), array('decorated' => false));

        $this->assertEquals(1, $exitCode);

        $this->assertStringContainsString('1 messages are missing in the incenteev_tests domain', $tester->getDisplay());
    }

    public function testWhitelistIsDomainBased()
    {
        $loader = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator');

        $loader->getCatalogue('en')->willReturn(new MessageCatalogue('en', array('messages' => array('foo' => 'bar', 'this key can go missing' => 'not defined in fr'))));
        $loader->getCatalogue('fr')->willReturn(new MessageCatalogue('fr', array('messages' => array('foo' => 'baz', 'this.one.also' => 'obsolete... or no'))));

        $command = new CompareCommand($loader->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('locale' => 'fr', '--whitelist-file' => __DIR__.'/../fixtures/whitelist.yml'), array('decorated' => false));

        $this->assertEquals(1, $exitCode);

        $this->assertStringContainsString('1 messages are obsolete in the messages domain', $tester->getDisplay());
        $this->assertStringContainsString('1 messages are missing in the messages domain', $tester->getDisplay());
    }
}
