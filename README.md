# Translation Checker Bundle

This bundle provides you a few CLI commands to check your translations.

[![Build Status](https://travis-ci.org/Incenteev/IncenteevTranslationCheckerBundle.svg)](https://travis-ci.org/Incenteev/IncenteevTranslationCheckerBundle)
[![Latest Stable Version](https://poser.pugx.org/incenteev/translation-checker-bundle/v/stable.svg)](https://packagist.org/packages/incenteev/translation-checker-bundle)
[![Latest Unstable Version](https://poser.pugx.org/incenteev/translation-checker-bundle/v/unstable.svg)](https://packagist.org/packages/incenteev/translation-checker-bundle)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/Incenteev/IncenteevTranslationCheckerBundle/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/Incenteev/IncenteevTranslationCheckerBundle/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/Incenteev/IncenteevTranslationCheckerBundle/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/Incenteev/IncenteevTranslationCheckerBundle/?branch=master)

## Installation

Installation is a quick (I promise!) 2 step process:

1. Download IncenteevTranslationCheckerBundle
2. Enable the Bundle

### Step 1: Install IncenteevTranslationCheckerBundle with composer

Run the following composer require command:

```bash
$ composer require incenteev/translation-checker-bundle:dev-master
```

### Step 2: Enable the bundle

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

### Step 3 (optional): Configure the bundle

To use the commands comparing the catalogue to the extracted translations, you
need to configure the bundles in which the templates should be parsed for translations:

```yaml
# app/config/config.yml
incenteev_translation_checker:
    extraction:
        bundles:
            - TwigBundle
            - AcmeDemoBundle
```

## Usage

The bundle provides a few CLI commands. To list them all, run:

```bash
$ php app/console list incenteev:translation
```
