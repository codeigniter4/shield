<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace Tests\Language;

use CodeIgniter\CLI\CLI;
use PHPUnit\Framework\TestCase;

/**
 * This abstract test class does the heavy testing of the sufficiency
 * and precision of provided per-locale translations.
 *
 * New tests for new locales should extend this class and marked as "final".
 *
 * @internal
 */
abstract class AbstractTranslationTestCase extends TestCase
{
    /**
     * Relative path to the main language folder in main repository.
     *
     * @var string
     */
    private const MAIN_LANGUAGE_REPO = '/src/Language/en/';

    /**
     * Relative path to the language folder.
     *
     * @var string
     */
    private const LANGUAGE_DIR = '/src/Language/';

    /**
     * Collection of all locale codes mapped from the
     * individual locale translation test case.
     *
     * @var array<string, string>
     */
    public static array $locales = [
        ArabicTranslationTest::class    => 'ar',
        BulgarianTranslationTest::class => 'bg',
        //        BosnianTranslationTest::class            => 'bs',
        CzechTranslationTest::class   => 'cs',
        GermanTranslationTest::class  => 'de',
        SpanishTranslationTest::class => 'es',
        FarsiTranslationTest::class   => 'fa',
        FrenchTranslationTest::class  => 'fr',
        //        HungarianTranslationTest::class          => 'hu',
        IndonesianTranslationTest::class => 'id',
        ItalianTranslationTest::class    => 'it',
        JapaneseTranslationTest::class   => 'ja',
        //        KoreanTranslationTest::class             => 'ko',
        LithuanianTranslationTest::class => 'lt',
        //        LatvianTranslationTest::class            => 'lv',
        //        MalayalamTranslationTest::class          => 'ml',
        //        DutchTranslationTest::class              => 'nl',
        //        NorwegianTranslationTest::class          => 'no',
        //        PolishTranslationTest::class             => 'pl',
        PortugueseTranslationTest::class => 'pt',
        BrazilianTranslationTest::class  => 'pt-BR',
        RussianTranslationTest::class    => 'ru',
        //        SinhalaTranslationTest::class            => 'si',
        SlovakTranslationTest::class  => 'sk',
        SerbianTranslationTest::class => 'sr',
        SwedishTranslationTest::class => 'sv-SE',
        //        ThaiTranslationTest::class               => 'th',
        TurkishTranslationTest::class   => 'tr',
        UkrainianTranslationTest::class => 'uk',
        //        VietnameseTranslationTest::class         => 'vi',
        //        SimplifiedChineseTranslationTest::class  => 'zh-CN',
        //        TraditionalChineseTranslationTest::class => 'zh-TW',
    ];

    /**
     * A list of language keys that do not differ from
     * the untranslated string even if translated correctly.
     *
     * This array will be filled in each locale's test
     * class and the contained values will be skipped in
     * testAllIncludedLanguageKeysAreTranslated.
     *
     * @var list<string>
     */
    protected array $excludedLocaleKeyTranslations = [];

    // -------------------------------------------------------------------------
    // TESTS
    // -------------------------------------------------------------------------

    /**
     * This tests that all language files configured in the main CI4 repository
     * have a corresponding language file in the current locale.
     *
     * @dataProvider localesProvider
     */
    final public function testAllConfiguredLanguageFilesAreTranslated(string $locale): void
    {
        $filesNotTranslated = array_diff(
            $this->expectedSets(),
            $this->foundSets($locale)
        );

        sort($filesNotTranslated);
        $count = count($filesNotTranslated);

        $this->assertEmpty($filesNotTranslated, sprintf(
            'Failed asserting that language %s "%s" in the main repository %s translated in "%s" locale.',
            $count > 1 ? 'files' : 'file',
            implode('", "', $filesNotTranslated),
            $count > 1 ? 'are' : 'is',
            $locale
        ));
    }

    /**
     * This tests that all translated language files in the current locale have a
     * corresponding language file in the main CI4 repository.
     *
     * @dataProvider localesProvider
     */
    final public function testAllTranslatedLanguageFilesAreConfigured(string $locale): void
    {
        $filesNotConfigured = array_diff(
            $this->foundSets($locale),
            $this->expectedSets()
        );

        sort($filesNotConfigured);
        $count = count($filesNotConfigured);

        $this->assertEmpty($filesNotConfigured, sprintf(
            'Failed asserting that translated language %s "%s" in "%s" locale %s configured in the main repository.',
            $count > 1 ? 'files' : 'file',
            implode('", "', $filesNotConfigured),
            $locale,
            $count > 1 ? 'are' : 'is'
        ));
    }

    /**
     * This tests that all language keys defined by a language file in the main CI4
     * repository have corresponding keys in the current locale.
     *
     * @dataProvider localesProvider
     */
    final public function testAllConfiguredLanguageKeysAreIncluded(string $locale): void
    {
        $keysNotIncluded = [];

        foreach ($this->foundSets($locale) as $file) {
            $missing = array_diff_key(
                $this->loadFile($file),
                $this->loadFile($file, $locale)
            );

            foreach (array_keys($missing) as $key) {
                $keysNotIncluded[] = substr($file, 0, -4) . '.' . $key;
            }
        }

        sort($keysNotIncluded);
        $count = count($keysNotIncluded);

        $this->assertEmpty($keysNotIncluded, sprintf(
            'Failed asserting that the language %s "%s" in the main repository %s included for translation in "%s" locale.',
            $count > 1 ? 'keys' : 'key',
            implode('", "', $keysNotIncluded),
            $count > 1 ? 'are' : 'is',
            $locale
        ));
    }

    /**
     * This tests that all included language keys in a language file for the current
     * locale have corresponding keys in the main CI4 repository.
     *
     * @dataProvider localesProvider
     */
    final public function testAllIncludedLanguageKeysAreConfigured(string $locale): void
    {
        $keysNotConfigured = [];

        foreach ($this->foundSets($locale) as $file) {
            $extra = array_diff_key(
                $this->loadFile($file, $locale),
                $this->loadFile($file)
            );

            foreach (array_keys($extra) as $key) {
                $keysNotConfigured[] = substr($file, 0, -4) . '.' . $key;
            }
        }

        sort($keysNotConfigured);
        $count = count($keysNotConfigured);

        $this->assertEmpty($keysNotConfigured, sprintf(
            'Failed asserting that the translated language %s "%s" in "%s" locale %s configured in the main repository.',
            $count > 1 ? 'keys' : 'key',
            implode('", "', $keysNotConfigured),
            $locale,
            $count > 1 ? 'are' : 'is'
        ));
    }

    /**
     * This tests that all included language keys in a language file for the current
     * locale that have corresponding keys in the main CI4 repository are really translated
     * and do not only copy the main repository's value.
     *
     * @dataProvider localesProvider
     */
    final public function testAllIncludedLanguageKeysAreTranslated(string $locale): void
    {
        // These keys are usually not translated because they contain either
        // universal abbreviations or simply combine parameters with signs.
        static $excludedKeyTranslations = [
        ];

        $excludedKeys  = array_unique(array_merge($excludedKeyTranslations, $this->excludedLocaleKeyTranslations));
        $availableSets = array_intersect($this->expectedSets(), $this->foundSets($locale));

        $keysNotTranslated = [];

        foreach ($availableSets as $file) {
            $originalStrings = $this->loadFile($file);

            foreach ($this->loadFile($file, $locale) as $key => $translation) {
                $keyName = substr($file, 0, -4) . '.' . $key;

                if (in_array($keyName, $excludedKeys, true)) {
                    continue;
                }

                if ((array_key_exists($key, $originalStrings) && $originalStrings[$key] === $translation) || $translation === '') {
                    $keysNotTranslated[] = $keyName;
                }
            }
        }

        sort($keysNotTranslated);
        $count = count($keysNotTranslated);

        $this->assertEmpty($keysNotTranslated, sprintf(
            'Failed asserting that the translated language %s "%s" in "%s" locale %s from the original keys in the main repository.',
            $count > 1 ? 'keys' : 'key',
            implode('", "', $keysNotTranslated),
            $locale,
            $count > 1 ? 'differ' : 'differs'
        ));
    }

    /**
     * This tests that the order of all language keys defined by a translation language file
     * resembles the order in the main CI4 repository.
     *
     * @dataProvider localesProvider
     */
    final public function testAllConfiguredLanguageKeysAreInOrder(string $locale): void
    {
        $diffs = [];

        foreach ($this->foundSets($locale) as $file) {
            $original   = $this->loadFile($file);
            $translated = $this->loadFile($file, $locale);

            // No need to check the order if the number is already different
            // This is handled by the other tests
            if (count($original) === count($translated)) {
                $trans = array_keys($translated);

                foreach (array_keys($original) as $index => $expectedKey) {
                    $actualKey = $trans[$index] ?? null;

                    if ($actualKey !== null && $expectedKey !== $actualKey) {
                        $diffs[] = sprintf(
                            "\n%s:\n%s\n%s",
                            $file,
                            CLI::color("-'{$expectedKey}' => '{$original[$expectedKey]}';", 'red'),
                            CLI::color("+'{$actualKey}' => '{$translated[$actualKey]}';", 'green')
                        );
                        break;
                    }
                }
            }
        }

        $this->assertEmpty($diffs, sprintf(
            "Failed asserting that the translated language keys in \"%s\" locale are ordered correctly.\n%s\n%s",
            $locale,
            CLI::color('--- Original', 'red') . "\n" . CLI::color('+++ Translated', 'green'),
            implode("\n", $diffs)
        ));
    }

    /**
     * @see https://codeigniter4.github.io/CodeIgniter4/outgoing/localization.html#replacing-parameters
     *
     * @dataProvider localesProvider
     */
    final public function testAllLocalizationParametersAreNotTranslated(string $locale): void
    {
        $diffs = [];

        foreach ($this->foundSets($locale) as $file) {
            $original   = $this->loadFile($file);
            $translated = $this->loadFile($file, $locale);

            foreach ($original as $key => $translation) {
                if (! array_key_exists($key, $translated)) {
                    continue;
                }

                preg_match_all('/(\{[^\}]+\})/', $translation, $matches);
                array_shift($matches);

                if ($matches === []) {
                    unset($matches);

                    continue;
                }

                foreach ($matches as $match) {
                    foreach ($match as $parameter) {
                        if (strpos($translated[$key], (string) $parameter) === false) {
                            $id = sprintf('%s.%s', substr($file, 0, -4), $key);

                            $diffs[$id] ??= [];

                            $diffs[$id][] = $parameter;
                        }
                    }
                }

                unset($matches);
            }
        }

        ksort($diffs);

        $this->assertEmpty($diffs, sprintf(
            "Failed asserting that parameters of translation keys are not translated:\n%s",
            implode("\n", array_map(
                static fn (string $key, array $values): string => sprintf('  * %s => %s', $key, implode(', ', $values)),
                array_keys($diffs),
                array_values($diffs)
            ))
        ));
    }

    /**
     * @return list<list<string>>
     */
    final public static function localesProvider(): iterable
    {
        $locale = self::$locales[static::class] ?? null;

        if (null === $locale) {
            static::fail('The locale code should be defined in the $locales property.');
        }

        return [$locale => [$locale]];
    }

    /**
     * @dataProvider localesProvider
     */
    final public function testLocaleHasCorrespondingTestCaseFile(string $locale): void
    {
        $class = array_flip(self::$locales)[$locale];

        $this->assertTrue(class_exists($class, false), sprintf(
            'Failed asserting that test class "%s" is existing.',
            $class
        ));
    }

    // -------------------------------------------------------------------------
    // UTILITIES
    // -------------------------------------------------------------------------

    /**
     * Get all the ISO 639-1 and 639-2 locale codes.
     *
     * @return array<string, list<string>>
     */
    final public function translationKeys(): array
    {
        helper('filesystem');

        $sets = [];
        $dirs = directory_map(getcwd() . '/Language', 1);

        foreach ($dirs as $dir) {
            $dir        = trim($dir, '\\/');
            $sets[$dir] = [$dir];
        }

        return $sets;
    }

    /**
     * @return array<string, string>
     */
    final public function expectedSets(): array
    {
        static $expected;

        if (null === $expected) {
            $expected = $this->translationSets();
        }

        return $expected;
    }

    /**
     * @return array<string, string>
     */
    final public function foundSets(string $locale): array
    {
        return $this->translationSets($locale);
    }

    /**
     * Loads the language keys and translation equivalents.
     *
     * @return array<string, string>
     */
    final public function loadFile(string $file, ?string $locale = null): array
    {
        $folder = $locale
            ? getcwd() . self::LANGUAGE_DIR . "{$locale}/"
            : getcwd() . self::MAIN_LANGUAGE_REPO;

        $file = $folder . $file;

        return require $file;
    }

    /**
     * Gets the set of language files for each location.
     *
     * @return array<string, string>
     */
    private function translationSets(?string $locale = null): array
    {
        helper('filesystem');

        $location = $locale
            ? getcwd() . self::LANGUAGE_DIR . "{$locale}/"
            : getcwd() . self::MAIN_LANGUAGE_REPO;

        $sets  = [];
        $files = directory_map($location, 1);

        foreach ($files as $file) {
            $sets[$file] = $file;
        }

        return $sets;
    }
}
