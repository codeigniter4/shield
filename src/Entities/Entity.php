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

namespace CodeIgniter\Shield\Entities;

use CodeIgniter\Entity\Entity as FrameworkEntity;
use CodeIgniter\Shield\Entities\Cast\IntBoolCast;

/**
 * Base Entity
 */
abstract class Entity extends FrameworkEntity
{
    /**
     * Custom convert handlers
     *
     * @var array<string, string>
     * @phpstan-var array<string, class-string>
     */
    protected $castHandlers = [
        'int_bool' => IntBoolCast::class,
    ];
}
