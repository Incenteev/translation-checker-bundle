<?php

namespace Incenteev\TranslationCheckerBundle\Tests\Command;

use Incenteev\TranslationCheckerBundle\Command\FindMissingCommand;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;
use Prophecy\PhpUnit\ProphecyTrait;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Translation\TranslatorBagInterface;

class FindMissingCommandTest extends TestCase
{
    use ProphecyTrait;

    /**
     * @param array<string, array<string, string>> $sourceMessages
     * @param array<string, array<string, string>> $extractedMessages
     * @param string|string[] $expectedMessages
     *
     * @dataProvider provideCommandData
     */
    public function testExecute(string $locale, array $sourceMessages, array $extractedMessages, int $expectedExitCode, $expectedMessages, int $verbosity = OutputInterface::VERBOSITY_NORMAL)
    {
        $loader = $this->prophesize(TranslatorBagInterface::class);
        $extractor = $this->prophesize('Incenteev\TranslationCheckerBundle\Translator\Extractor\ExtractorInterface');

        $loader->getCatalogue($locale)->willReturn(new MessageCatalogue($locale, $sourceMessages));

        $extractor->extract(Argument::type('Symfony\Component\Translation\MessageCatalogue'))->will(function ($args) use ($extractedMessages) {
            /** @var MessageCatalogue $catalogue */
            $catalogue = $args[0];

            $catalogue->addCatalogue(new MessageCatalogue($catalogue->getLocale(), $extractedMessages));
        });

        $command = new FindMissingCommand($loader->reveal(), $extractor->reveal());

        $tester = new CommandTester($command);
        $exitCode = $tester->execute(array('locale' => $locale), array('decorated' => false, 'verbosity' => $verbosity));

        $this->assertEquals($expectedExitCode, $exitCode);

        foreach ((array) $expectedMessages as $message) {
            $this->assertStringContainsString($message, $tester->getDisplay());
        }
    }

    public function provideCommandData(): iterable
    {
        return array(
            'sync with en' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                array('messages' => array('foo' => 'baz')),
                0,
                'The en catalogue is in sync with the extracted one.',
            ),
            'extra messages' => array(
                'en',
                array('messages' => array('foo' => 'bar'), 'test' => array('hello' => 'world')),
                array('messages' => array('foo' => 'baz')),
                0,
                'The en catalogue is in sync with the extracted one.',
                OutputInterface::VERBOSITY_VERBOSE,
            ),
            'missing message' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                array('messages' => array('foo' => 'bar'), 'test' => array('hello' => 'world')),
                1,
                '1 messages are missing in the test domain',
            ),
            'missing message verbose' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                array('messages' => array('foo' => 'bar'), 'test' => array('hello' => 'world')),
                1,
                array('1 messages are missing in the test domain', '    hello'),
                OutputInterface::VERBOSITY_VERBOSE,
            ),
            'missing multiple domains' => array(
                'en',
                array('messages' => array('foo' => 'bar')),
                array('messages' => array('foo' => 'bar', 'bar' => 'baz'), 'test' => array('hello' => 'world', 'world' => 'wide')),
                1,
                array('1 messages are missing in the messages domain', '2 messages are missing in the test domain'),
            ),
        );
    }
}
