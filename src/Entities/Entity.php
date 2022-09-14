<?php

declare(strict_types=1);

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
