# Translation Checker Bundle

This bundle provides you a few CLI commands to check your translations.
These commands are designed to be usable easily in CI jobs

[![CI](https://github.com/Incenteev/translation-checker-bundle/actions/workflows/ci.yml/badge.svg)](https://github.com/Incenteev/translation-checker-bundle/actions/workflows/ci.yml)
[![Latest Stable Version](https://poser.pugx.org/incenteev/translation-checker-bundle/v/stable.svg)](https://packagist.org/packages/incenteev/translation-checker-bundle)
[![Latest Unstable Version](https://poser.pugx.org/incenteev/translation-checker-bundle/v/unstable.svg)](https://packagist.org/packages/incenteev/translation-checker-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Incenteev/translation-checker-bundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Incenteev/translation-checker-bundle/?branch=master)

## Installation

Installation is a quick (I promise!) 2 step process:

1. Download IncenteevTranslationCheckerBundle
2. Enable the Bundle

### Step 1: Install IncenteevTranslationCheckerBundle with composer

Run the following composer require command:

```bash
$ composer require incenteev/translation-checker-bundle
```

### Step 2: Enable the bundle

> **Note:** If you use Flex, you have nothing to do at this step, as Flex does it for you.

Finally, enable the bundle in the kernel:

```php
// app/AppKernel.php

public function registerBundles()
{
    $bundles = array(
        // ...
        new Incenteev\TranslationCheckerBundle\IncenteevTranslationCheckerBundle(),
    );
}
```

> **Warning:** This bundle requires that the translator is enabled in FrameworkBundle.

## Usage

The bundle provides a few CLI commands. To list them all, run:

```bash
$ bin/console list incenteev:translation
```

All commands display a summary only by default. Run then in verbose mode
to get a detailed report.

### Finding missing translations

The `incenteev:translation:find-missing` command extracts necessary translations
from our app source code, and then compare this list to the translation available
for the tested locale. It will exit with a failure exit code if any missing
translation is detected.

> **Warning:** Translation extraction will not find all translations used by our app.
> So while a failure exit code means there is an issue, a success exit code does not
> guarantee that all translations are available.
> The recommended usage is to use this command for your reference locale only, and
> then test other locales by comparing them against the reference instead.

### Comparing translations to a reference locale

The `incenteev:translation:compare` command compares available translations from
2 different locales and will exit with a failure exit code if catalogues are not
in sync.

> Note: this command may not work well for country variants of a locale (`fr_FR`).
> Use it for main locales.

## Configuration

To use the commands comparing the catalogue to the extracted translations, you
need to configure the bundles in which the templates should be parsed for translations.
By default, only templates in `templates` (and `app/Resources/views` on Symfony 4 and older)
are registered in the extractor. You can register bundles that will be processed too.

```yaml
# app/config/config.yml
incenteev_translation_checker:
    extraction:
        bundles:
            - TwigBundle
            - AcmeDemoBundle
```

The bundle also supports extracting translations from JS files, for projects using
[willdurand/js-translation-bundle](https://packagist.org/packages/willdurand/js-translation-bundle):

```yaml
# app/config/config.yml
incenteev_translation_checker:
    extraction:
        js:
            # Paths in which JS files should be checked for translations.
            # Path could be either for files, or for directories in which JS files should be looked for.
            # This configuration is required to enable this feature.
            paths:
                - '%kernel.project_dir%/web/js'
                - '%kernel.project_dir%/web/other.js'
            # The default domain used in your JS translations. Should match the js-translation-bundle configuration
            # Defaults to 'messages'
            default_domain: js
```
