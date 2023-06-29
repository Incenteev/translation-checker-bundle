<?php

namespace Incenteev\TranslationCheckerBundle\Translator\Extractor;

use Symfony\Component\Translation\MessageCatalogue;

interface ExtractorInterface
{
    /**
     * Extracts the messages into the given catalogue.
     *
     * @return void
     */
    public function extract(MessageCatalogue $catalogue);
}
