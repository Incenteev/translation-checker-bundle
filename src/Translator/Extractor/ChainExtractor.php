<?php

namespace Incenteev\TranslationCheckerBundle\Translator\Extractor;

use Symfony\Component\Translation\MessageCatalogue;

final class ChainExtractor implements ExtractorInterface
{
    /**
     * @var ExtractorInterface[]
     */
    private $extractors;

    /**
     * @param ExtractorInterface[] $extractors
     */
    public function __construct(array $extractors)
    {
        $this->extractors = $extractors;
    }

    public function extract(MessageCatalogue $catalogue)
    {
        foreach ($this->extractors as $extractor) {
            $extractor->extract($catalogue);
        }
    }
}
