<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Entities\Cast;

use CodeIgniter\Entity\Cast\BaseCast;

/**
 * Int Bool Cast
 *
 * DB column: int (0/1) <--> Class property: bool
 */
final class IntBoolCast extends BaseCast
{
    /**
     * @param int $value
     */
    public static function get($value, array $params = []): bool
    {
        return (bool) $value;
    }

    /**
     * @param bool|int|string $value
     */
    public static function set($value, array $params = []): int
    {
        return (int) $value;
    }
}
