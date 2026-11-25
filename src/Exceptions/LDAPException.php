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

namespace CodeIgniter\Shield\Exceptions;

/**
 * Reports LDAP failures using sanitized diagnostic reason codes.
 */
final class LDAPException extends RuntimeException
{
    /**
     * Localizes the message for the supplied diagnostic reason code.
     */
    public function __construct(public readonly string $reason)
    {
        parent::__construct(lang('Auth.ldapOperationFailed', [$reason]));
    }
}
