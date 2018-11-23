<?php

namespace Incenteev\TranslationCheckerBundle\Command;

use Incenteev\TranslationCheckerBundle\Translator\ExposingTranslator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Translation\Catalogue\TargetOperation;
use Symfony\Component\Translation\MessageCatalogue;
use Symfony\Component\Yaml\Yaml;

class CompareCommand extends Command
{
    private $exposingTranslator;

    public function __construct(ExposingTranslator $exposingTranslator)
    {
        parent::__construct();

        $this->exposingTranslator = $exposingTranslator;
    }

    protected function configure()
    {
        $this->setName('incenteev:translation:compare')
            ->setDescription('Compares two translation catalogues to ensure they are in sync')
            ->addArgument('locale', InputArgument::REQUIRED, 'The locale being checked')
            ->addArgument('source', InputArgument::OPTIONAL, 'The source of the comparison', 'en')
            ->addOption('obsolete-only', null, InputOption::VALUE_NONE, 'Report only obsolete keys')
            ->addOption('domain', 'd', InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, 'The domains being compared')
            ->addOption('whitelist-file', 'w', InputOption::VALUE_REQUIRED, 'Path to a YAML whitelist file')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command compares 2 translation catalogues to
ensure they are in sync. If there is missing keys or obsolete keys in the target
catalogue, the command will exit with an error code.

When running the command in verbose mode, the translation keys will also be displayed.
<info>php %command.full_name% fr --verbose</info>

The <info>--domain</info> option allows to restrict the domains being checked.
It can be specified several times to check several domains. If the option is not passed,
all domains will be compared.

The <info>--obsolete-only</info> option allows to check only obsolete keys, and ignore any
missing keys.

The <info>--whitelist-file</info> option allows to define a whitelist of keys which are
ignored from the comparison (they are never reported as missing or as obsolete). This
file must be a Yaml file where keys are domains, and values are an array of whitelisted
EOF
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sourceCatalogue = $this->exposingTranslator->getCatalogue($input->getArgument('source'));
        $comparedCatalogue = $this->exposingTranslator->getCatalogue($input->getArgument('locale'));

        // Change the locale of the catalogue as DiffOperation requires operating on a single locale
        $catalogue = new MessageCatalogue($sourceCatalogue->getLocale(), $comparedCatalogue->all());

        $operation = new TargetOperation($catalogue, $sourceCatalogue);

        $domains = $operation->getDomains();
        $restrictedDomains = $input->getOption('domain');
        if (!empty($restrictedDomains)) {
            $domains = array_intersect($domains, $restrictedDomains);
            $output->writeln(sprintf('<comment>Checking the domains %s</comment>', implode(', ', $domains)));
        }

        $checkMissing = !$input->getOption('obsolete-only');

        $whitelistFile = $input->getOption('whitelist-file');
        $whitelist = array();

        if (null !== $whitelistFile) {
            if (!file_exists($whitelistFile)) {
                $output->writeln(sprintf('<error>The whitelist file "%s" does not exist.</error>', $whitelistFile));

                return 1;
            }

            $whitelist = Yaml::parse(file_get_contents($whitelistFile));

            if (!is_array($whitelist)) {
                $output->writeln(sprintf('<error>The whitelist file "%s" is invalid. It must be a Yaml file containing a map.</error>', $whitelistFile));

                return 1;
            }
        }

        $valid = true;

        foreach ($domains as $domain) {
            $missingMessages = $checkMissing ? $operation->getNewMessages($domain) : array();
            $obsoleteMessages = $operation->getObsoleteMessages($domain);
            $written = false;

            if (isset($whitelist[$domain])) {
                $domainWhitelist = array_flip($whitelist[$domain]);
                $missingMessages = array_diff_key($missingMessages, $domainWhitelist);
                $obsoleteMessages = array_diff_key($obsoleteMessages, $domainWhitelist);
            }

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
