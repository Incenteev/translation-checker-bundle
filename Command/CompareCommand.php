<?php

namespace Incenteev\TranslationCheckerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\DiffOperation;
use Symfony\Component\Translation\MessageCatalogue;

class CompareCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('incenteev:translation:compare')
            ->setDescription('Compares two translation catalogues to ensure they are in sync')
            ->addArgument('locale', InputArgument::REQUIRED, 'The locale being checked')
            ->addArgument('source', InputArgument::OPTIONAL, 'The source of the comparison', 'en');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = $this->getContainer()->get('incenteev_translation_checker.exposing_translator');

        $sourceCatalogue = $loader->getCatalogue($input->getArgument('source'));
        $comparedCatalogue = $loader->getCatalogue($input->getArgument('locale'));

        // Change the locale of the catalogue as DiffOperation requires operating on a single locale
        $catalogue = new MessageCatalogue($sourceCatalogue->getLocale(), $comparedCatalogue->all());

        $operation = new DiffOperation($catalogue, $sourceCatalogue);

        $valid = true;

        foreach ($operation->getDomains() as $domain) {
            $missingMessages = $operation->getNewMessages($domain);
            $obsoleteMessages = $operation->getObsoleteMessages($domain);
            $written = false;

            if (!empty($missingMessages)) {
                $valid = false;
                $written = true;
                $output->writeln(sprintf('<comment>%s</comment> messages are missing in the <info>%s</info> domain', count($missingMessages), $domain));

                $this->displayMessages($output, $missingMessages);
            }

            if (!empty($obsoleteMessages)) {
                $valid = false;
                $written = true;
                $output->writeln(sprintf('<comment>%s</comment> messages are obsolete in the <info>%s</info> domain', count($obsoleteMessages), $domain));

                $this->displayMessages($output, $obsoleteMessages);
            }

            if ($written) {
                $output->writeln('');
            }
        }

        if ($valid) {
            $output->writeln(sprintf(
                '<info>The <comment>%s</comment> catalogue is in sync with the <comment>%s</comment> one.</info>',
                $input->getArgument('locale'),
                $input->getArgument('source')
            ));

            return 0;
        }

        return 1;
    }

    private function displayMessages(OutputInterface $output, array $messages)
    {
        if ($output->getVerbosity() < OutputInterface::VERBOSITY_VERBOSE) {
            return;
        }

        foreach ($messages as $key => $translation) {
            $output->writeln('    '.$key);
        }
        $output->writeln('');
    }
}
