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

namespace CodeIgniter\Shield\Authorization;

/**
 * Matches permission grants against requested permission names for Shield authorization internals.
 */
final class PermissionMatcher
{
    /**
     * @param list<string> $grants
     */
    public static function matches(string $permission, array $grants): bool
    {
        if (! self::isValid($permission)) {
            return false;
        }

        foreach ($grants as $grant) {
            if (! self::isValid($grant)) {
                continue;
            }

            if ($grant === $permission) {
                return true;
            }

            if (str_contains($grant, '*') && self::matchesWildcardGrant($grant, $permission)) {
                return true;
            }
        }

        return false;
    }

    private static function matchesWildcardGrant(string $grant, string $permission): bool
    {
        $grantSegments      = explode('.', $grant);
        $permissionSegments = explode('.', $permission);

        if (end($grantSegments) === '*') {
            array_pop($grantSegments);

            // Root labels like `admin` are not permission scopes, so `admin.*` should not grant `admin`.
            if (count($grantSegments) === 1 && count($permissionSegments) === 1) {
                return false;
            }

            return count($permissionSegments) >= count($grantSegments)
                && self::segmentsMatch($grantSegments, array_slice($permissionSegments, 0, count($grantSegments)));
        }

        return self::segmentsMatch($grantSegments, $permissionSegments);
    }

    /**
     * @param list<string> $grantSegments
     * @param list<string> $permissionSegments
     */
    private static function segmentsMatch(array $grantSegments, array $permissionSegments): bool
    {
        if (count($grantSegments) !== count($permissionSegments)) {
            return false;
        }

        foreach ($grantSegments as $index => $grantSegment) {
            if ($grantSegment !== '*' && $grantSegment !== $permissionSegments[$index]) {
                return false;
            }
        }

        return true;
    }

    private static function isValid(string $permission): bool
    {
        $segments = explode('.', $permission);

        if ($segments === ['*'] || $segments[0] === '*') {
            return false;
        }

        foreach ($segments as $segment) {
            if ($segment === '' || ($segment !== '*' && str_contains($segment, '*'))) {
                return false;
            }
        }

        return true;
    }
}
