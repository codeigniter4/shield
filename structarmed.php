<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Boundwize\StructArmed\Preset\Presets\Psr4Preset;
use Boundwize\StructArmed\Rule\Rules\Class_\ExtendedClassMustBeAbstractOrInstantiatedRule;
use Boundwize\StructArmed\Rule\Rules\Class_\MustBeFinalRule;

return Architecture::define()
    ->rule(
        'tests.must_be_final',
        new MustBeFinalRule(layer: 'tests'),
    )
    ->skip([
        Psr4Preset::CLASSES_MUST_MATCH_COMPOSER => [
            __DIR__ . '/src/Database/Migrations',
        ],
    ])
    ->cacheDirectory(is_dir('/tmp') ? '/tmp/structarmed' : null)
    ->withPresets(Preset::PSR4(), Preset::CODEQUALITY())

    ->rule('base_classes.must_be_abstract', new ExtendedClassMustBeAbstractOrInstantiatedRule('Source', '/^CodeIgniter\\\\Shield\\\\.*Base.*$/'))

    ->layer('tests', __DIR__ . '/tests')
    ->layerPattern('Model', '/^CodeIgniter\\\\Shield\\\\.*Model$/')
    ->layerPattern('Controller', '/^CodeIgniter\\\\Shield\\\\Controllers\\\\.*$/')
    ->layerPattern('Config', '/^CodeIgniter\\\\Shield\\\\Config\\\\.*$/', '/^.*Services$/')
    ->layerPattern('Entity', '/^CodeIgniter\\\\Shield\\\\Entities\\\\.*$/')
    ->layerPattern('Service', '/^.*Services$/')
    ->ruleset([
        'Entity'  => ['Config', 'Model', 'Service'],
        'Config'  => ['Model', 'Service'],
        'Model'   => ['Config', 'Entity', 'Service'],
        'Service' => ['Config'],
    ]);
