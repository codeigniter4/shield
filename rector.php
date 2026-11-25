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

use Rector\CodeQuality\Rector\BooleanAnd\SimplifyEmptyArrayCheckRector;
use Rector\CodeQuality\Rector\Empty_\SimplifyEmptyCheckOnEmptyArrayRector;
use Rector\CodeQuality\Rector\Expression\InlineIfToExplicitIfRector;
use Rector\CodeQuality\Rector\FuncCall\ChangeArrayPushToArrayAssignRector;
use Rector\CodeQuality\Rector\FuncCall\SingleInArrayToCompareRector;
use Rector\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector;
use Rector\CodeQuality\Rector\Ternary\TernaryEmptyArrayArrayDimFetchToCoalesceRector;
use Rector\CodeQuality\Rector\Ternary\UnnecessaryTernaryExpressionRector;
use Rector\CodingStyle\Rector\ClassMethod\FuncGetArgsToVariadicParamRector;
use Rector\CodingStyle\Rector\ClassMethod\MakeInheritedMethodVisibilitySameAsParentRector;
use Rector\CodingStyle\Rector\FuncCall\VersionCompareFuncCallToConstantRector;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\Cast\RecastingRemovalRector;
use Rector\DeadCode\Rector\ClassMethod\RemoveUnusedPromotedPropertyRector;
use Rector\DeadCode\Rector\If_\UnwrapFutureCompatibleIfPhpVersionRector;
use Rector\DeadCode\Rector\MethodCall\RemoveNullArgOnNullDefaultParamRector;
use Rector\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\Php55\Rector\String_\StringClassNameToClassConstantRector;
use Rector\Php73\Rector\FuncCall\StringifyStrNeedlesRector;
use Rector\PHPUnit\CodeQuality\Rector\Class_\YieldDataProviderRector;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertEmptyNullableObjectToAssertInstanceofRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Privatization\Rector\Property\PrivatizeFinalClassPropertyRector;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src/',
        __DIR__ . '/tests/',
    ])
    ->withAutoloadPaths([
        __DIR__ . '/vendor/autoload.php',
    ])
    ->withBootstrapFiles([
        realpath(getcwd()) . '/vendor/codeigniter4/framework/system/Test/bootstrap.php',
        // Load Config\Migrations before PHPStan's reflection can intercept its autoload.
        realpath(getcwd()) . '/vendor/codeigniter4/framework/app/Config/Migrations.php',
    ])
    ->withPHPStanConfigs([
        __DIR__ . '/phpstan.dist.neon',
        __DIR__ . '/vendor/codeigniter/phpstan-codeigniter/extension.neon',
        __DIR__ . '/vendor/phpstan/phpstan-strict-rules/rules.neon',
    ])
    ->withCache(is_dir('/tmp') ? '/tmp/rector' : null)
    ->withParallel()
    ->withImportNames(removeUnusedImports: false)
    ->withPhpSets(php82: true)
    ->withSets([
        SetList::DEAD_CODE,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withSkip([
        __DIR__ . '/src/Views',

        StringifyStrNeedlesRector::class,
        YieldDataProviderRector::class,

        RemoveUnusedPromotedPropertyRector::class,

        // May load view files directly when detecting classes
        StringClassNameToClassConstantRector::class,

        // Ignore tests that use CodeIgniter::CI_VERSION
        UnwrapFutureCompatibleIfPhpVersionRector::class => [
            __DIR__ . '/src/Test/MockInputOutput.php',
            __DIR__ . '/tests/Authentication/Authenticators/SessionAuthenticatorTest.php',
            __DIR__ . '/tests/Commands/SetupTest.php',
            __DIR__ . '/tests/Commands/UserModelGeneratorTest.php',
            __DIR__ . '/tests/Controllers/ActionsTest.php',
            __DIR__ . '/tests/Controllers/LoginTest.php',
            __DIR__ . '/tests/Controllers/MagicLinkTest.php',
        ],
        RemoveUnusedPrivatePropertyRector::class => [
            __DIR__ . '/src/Test/MockInputOutput.php',
            __DIR__ . '/tests/Commands/SetupTest.php',
            __DIR__ . '/tests/Commands/UserModelGeneratorTest.php',
            __DIR__ . '/tests/Controllers/LoginTest.php',
        ],

        RecastingRemovalRector::class => [
            // To check old Email Config file
            __DIR__ . '/src/Commands/Setup.php',
        ],

        // Ignore some PHPUnit rules
        AssertEmptyNullableObjectToAssertInstanceofRector::class,

        // Ignore for readability
        RemoveNullArgOnNullDefaultParamRector::class,
    ])
    ->withRules([
        RemoveAlwaysElseRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        SimplifyIfReturnBoolRector::class,
        InlineIfToExplicitIfRector::class,
        PreparedValueToEarlyReturnRector::class,
        ChangeArrayPushToArrayAssignRector::class,
        UnnecessaryTernaryExpressionRector::class,
        FuncGetArgsToVariadicParamRector::class,
        MakeInheritedMethodVisibilitySameAsParentRector::class,
        SimplifyEmptyArrayCheckRector::class,
        SimplifyEmptyCheckOnEmptyArrayRector::class,
        TernaryEmptyArrayArrayDimFetchToCoalesceRector::class,
        PrivatizeFinalClassPropertyRector::class,
        SingleInArrayToCompareRector::class,
        VersionCompareFuncCallToConstantRector::class,
    ]);
