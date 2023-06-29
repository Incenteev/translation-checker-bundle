<?php

namespace Incenteev\TranslationCheckerBundle\Command;

use Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator;
use Incenteev\TranslationCheckerBundle\Translator\Extractor\ExtractorInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\MessageCatalogue;

/**
 * @final
 */
class FindMissingCommand extends Command
{
    private $exposingTranslator;
    private $extractor;

    public function __construct(ExposingTranslator $exposingTranslator, ExtractorInterface $extractor)
    {
        parent::__construct();
        $this->exposingTranslator = $exposingTranslator;
        $this->extractor = $extractor;
    }

    protected function configure(): void
    {
        $this->setName('incenteev:translation:find-missing')
            ->setDescription('Finds the missing translations in a catalogue')
            ->addArgument('locale', InputArgument::REQUIRED, 'The locale being checked')
            ->setHelp(<<<HELP
The <info>%command.name%</info> command extracts translation strings from templates
of a given bundle and checks if they are available in the catalogue.

<info>php %command.full_name% en</info>

<comment>This command can only identify missing string among the string detected
by the translation extractor.</comment>
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $extractedCatalogue = new MessageCatalogue($input->getArgument('locale'));
        $this->extractor->extract($extractedCatalogue);

        $loadedCatalogue = $this->exposingTranslator->getCatalogue($input->getArgument('locale'));

        $operation = new TargetOperation($loadedCatalogue, $extractedCatalogue);

        $valid = true;

        foreach ($operation->getDomains() as $domain) {
            $messages = $operation->getNewMessages($domain);

            if (empty($messages)) {
                continue;
            }

            $valid = false;
            $output->writeln(sprintf('<comment>%s</comment> messages are missing in the <info>%s</info> domain', count($messages), $domain));

            if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
                continue;
            }

            foreach ($messages as $key => $translation) {
                $output->writeln('    '.$key);
            }
            $output->writeln('');
        }

        if ($valid) {
            $output->writeln(sprintf(
                '<info>The <comment>%s</comment> catalogue is in sync with the extracted one.</info>',
                $input->getArgument('locale')
            ));

            return 0;
        }

        return 1;
    }
}
