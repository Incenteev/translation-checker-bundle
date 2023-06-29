<?php

namespace Incenteev\TranslationCheckerBundle\Translator\Extractor;

use Symfony\Component\Translation\Extractor\ExtractorInterface as SymfonyExtractorInterface;
use Symfony\Component\Translation\MessageCatalogue;

class SymfonyExtractor implements ExtractorInterface
{
    private SymfonyExtractorInterface $extractor;
    /**
     * @var string[]
     */
    private array $paths;

    /**
     * @param string[] $paths
     */
    public function __construct(SymfonyExtractorInterface $extractor, array $paths)
    {
        $this->extractor = $extractor;
        $this->paths = $paths;
    }

    public function extract(MessageCatalogue $catalogue): void
    {
        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                continue;
            }

            $this->extractor->extract($path, $catalogue);
        }
    }
}
