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
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Authentication\Filters\AbstractFilterTestCase;
use Tests\Support\DatabaseTestCase;

return PyrameterConfig::create()
    ->usesClass(
        DatabaseTestTrait::class,
        TestKind::Integration,
        unless: [FeatureTestTrait::class, AbstractFilterTestCase::class],
    )
    ->usesClass(
        DatabaseTestCase::class,
        TestKind::Integration,
        unless: [FeatureTestTrait::class, AbstractFilterTestCase::class],
    )
    ->usesClass(
        FeatureTestTrait::class,
        TestKind::Functional,
    )
    ->usesClass(
        AbstractFilterTestCase::class,
        TestKind::Functional,
    )
    ->usesFunction('copy', TestKind::Integration)
    ->usesFunction('file_get_contents', TestKind::Integration)
    ->usesFunction('getcwd', TestKind::Integration)
    ->usesFunction('is_file', TestKind::Integration)
    ->usesFunction('unlink', TestKind::Integration)
    ->targetShape(
        unit: ['min' => 40],
        functional: ['max' => 20],
        integration: ['max' => 50],
        e2e: ['max' => 0],
    )
    ->failOnViolation();
