<?php

namespace Incenteev\TranslationCheckerBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addArgument('source', InputArgument::OPTIONAL, 'The source of the comparison', 'en')
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The domains being compared')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command compares 2 translation catalogues to
ensure they are in sync. If there is missing keys or obsolete keys in the target
catalogue, the command will exit with an error code.

When running the command in verbose mode, the translation keys will also be displayed.
<info>php %command.full_name% fr --verbose</info>

The <info>--domain</info> option allows to restrict the domains being checked.
It can be specified several times to check several domains. If the option is not passed,
all domains will be compared.
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $loader = $this->getContainer()->get('incenteev_translation_checker.exposing_translator');

        $sourceCatalogue = $loader->getCatalogue($input->getArgument('source'));
        $comparedCatalogue = $loader->getCatalogue($input->getArgument('locale'));

        // Change the locale of the catalogue as DiffOperation requires operating on a single locale
        $catalogue = new MessageCatalogue($sourceCatalogue->getLocale(), $comparedCatalogue->all());

        $operation = new DiffOperation($catalogue, $sourceCatalogue);

        $domains = $operation->getDomains();
        $restrictedDomains = $input->getOption('domain');
        if (!empty($restrictedDomains)) {
            $domains = array_intersect($domains, $restrictedDomains);
            $output->writeln(sprintf('<comment>Checking the domains %s</comment>', implode(', ', $domains)));
        }

        $valid = true;

        foreach ($domains as $domain) {
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
