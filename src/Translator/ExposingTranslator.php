<?php

namespace Incenteev\TranslationCheckerBundle\Translator;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;
use Symfony\Component\Translation\MessageCatalogueInterface;

/**
 * Class extending the translator to expose the catalogue loading.
 * @internal
 */
class ExposingTranslator extends Translator
{
    public function getCatalogue($locale = null): MessageCatalogueInterface
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        }

        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }
}
