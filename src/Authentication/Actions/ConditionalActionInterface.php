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

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\Shield\Entities\User;

/**
 * Allows an authentication action to decide if it applies to a user.
 */
interface ConditionalActionInterface
{
    /**
     * Determines if this action applies to the given user.
     *
     * This method may be called while Shield starts or discovers pending actions.
     * It should be deterministic and free of side effects.
     */
    public function appliesTo(User $user): bool;
}
