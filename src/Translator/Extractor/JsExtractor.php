<?php

namespace Incenteev\TranslationCheckerBundle\Translator\Extractor;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Translation\MessageCatalogue;

class JsExtractor implements ExtractorInterface
{
    /**
     * @var string[]
     */
    private array $paths;
    private string $defaultDomain;

    /**
     * @param string[] $paths
     */
    public function __construct(array $paths, string $defaultDomain)
    {
        $this->paths = $paths;
        $this->defaultDomain = $defaultDomain;
    }

    public function extract(MessageCatalogue $catalogue): void
    {
        $directories = array();

        foreach ($this->paths as $path) {
            if (is_dir($path)) {
                $directories[] = $path;

                continue;
            }

            if (is_file($path)) {
                $contents = file_get_contents($path);
                if ($contents === false) {
                    throw new \RuntimeException(sprintf('Failed to read the file "%s"', $path));
                }

                $this->extractTranslations($catalogue, $contents);
            }
        }

        if ($directories) {
            $finder = new Finder();

            $finder->files()
                ->in($directories)
                ->name('*.js');

            foreach ($finder as $file) {
                $this->extractTranslations($catalogue, $file->getContents());
            }
        }
    }

    private function extractTranslations(MessageCatalogue $catalogue, string $fileContent): void
    {
        $pattern = <<<REGEXP
/
\.trans(?:Choice)?\s*+\(\s*+
(?:
    '([^']++)' # single-quoted string
    |
    "([^"]++)" # double-quoted string
)
\s*+[,)] # followed by a comma (for next arguments) or the closing parenthesis, to be sure we got the whole argument (no concatenation to something else)
/x
REGEXP;

        preg_match_all($pattern, $fileContent, $matches);

        foreach ($matches[1] as $match) {
            if (empty($match)) {
                continue;
            }

            $catalogue->set($match, $match, $this->defaultDomain);
        }

        foreach ($matches[2] as $match) {
            if (empty($match)) {
                continue;
            }

            $catalogue->set($match, $match, $this->defaultDomain);
        }
    }
}
