# 1.3.1 (2018-02-20)

* Added support for Symfony 4

# 1.3.0 (2017-08-04)

* Added support for chaining multiple extractors. New extractors can be registered using the `incenteev_translation_checker.extractor` tag
* Added support for autoconfiguration for custom extractors in Symfony 3.3+ (adding the tag implicitly)
* Added a JS extractor, to extract translation from JS files when using willdurand/js-translation-bundle. See https://github.com/Incenteev/IncenteevTranslationCheckerBundle/pull/18 for current limitations
* Removed tests and development files from the ZIP archive to make the download smaller.

## 1.2.1 (2017-06-12)

* Fixed compatibility with Symfony 3.3+

## 1.2.0 (2017-02-21)

* Added support for Symfony 3
* Dropped support for Symfony 2.7 and older

## 1.1.0 (2015-09-29)

Features:

* Added the `--only-obsolete` flag in `incenteev:translation:compare` to check only obsolete keys

Bugfix:

* Fixed compatibility with Symfony 2.7+

## 1.0.0 (2015-06-08)

Initial stable release
