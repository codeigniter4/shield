<?php

declare(strict_types=1);

namespace Tests\Language;

/**
 * @internal
 */
final class BrazilianTranslationTest extends AbstractTranslationTestCase
{
    protected array $excludedLocaleKeyTranslations = [
        'Auth.password',
        'Auth.login',
    ];
}
