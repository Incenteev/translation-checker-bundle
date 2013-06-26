<?php

namespace Incenteev\TranslationCheckerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\DiffOperation;
use Symfony\Component\Translation\MessageCatalogue;

class FindMissingCommand extends ContainerAwareCommand
{
    protected function configure()
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $extractedCatalogue = new MessageCatalogue($input->getArgument('locale'));
        $extractor = $this->getContainer()->get('incenteev_translation_checker.extractor');
        $extractor->extract($extractedCatalogue);

        $loader = $this->getContainer()->get('incenteev_translation_checker.exposing_translator');
        $loadedCatalogue = $loader->getCatalogue($input->getArgument('locale'));

        $operation = new DiffOperation($loadedCatalogue, $extractedCatalogue);

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
