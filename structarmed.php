<?php

declare(strict_types=1);

use Boundwize\StructArmed\Architecture;
use Boundwize\StructArmed\Preset\Preset;
use Boundwize\StructArmed\Preset\Presets\Psr4Preset;

return Architecture::define()
    ->skip([
        Psr4Preset::CLASSES_MUST_MATCH_COMPOSER => [
            __DIR__ . '/src/Database/Migrations',
        ],
    ])
    ->cacheDirectory(is_dir('/tmp') ? '/tmp/structarmed' : null)
    ->withPreset(Preset::PSR4())
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
