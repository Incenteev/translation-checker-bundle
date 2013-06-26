<?php

namespace Incenteev\TranslationCheckerBundle\Translator;

use Symfony\Bundle\FrameworkBundle\Translation\Translator;

/**
 * Class extending the translator to expose the catalogue loading
 */
class ExposingTranslator extends Translator
{
    public function getCatalogue($locale)
    {
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }

        return $this->catalogues[$locale];
    }
}
