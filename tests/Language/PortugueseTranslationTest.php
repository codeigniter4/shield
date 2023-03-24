<?php

declare(strict_types=1);

namespace Tests\Language;

/**
 * @internal
 */
final class PortugueseTranslationTest extends AbstractTranslationTestCase
{
    protected array $excludedLocaleKeyTranslations = [
        'Auth.login',
    ];
}
