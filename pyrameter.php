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

use Boundwize\Pyrameter\Config\PyrameterConfig;
use Boundwize\Pyrameter\TestKind;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\DatabaseTestCase;

return PyrameterConfig::defaults()
    ->usesClass(
        DatabaseTestCase::class,
        TestKind::Integration,
        unless: [FeatureTestTrait::class],
    )
    ->usesClass(
        FeatureTestTrait::class,
        TestKind::Functional,
    )
    ->targetShape(
        unit: ['min' => 40],
        functional: ['max' => 10],
        integration: ['max' => 50],
        e2e: ['max' => 0],
    )
    ->failOnViolation();
