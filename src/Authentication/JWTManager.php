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

namespace CodeIgniter\Shield\Authentication;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\JWT\JWSDecoder;
use CodeIgniter\Shield\Authentication\JWT\JWSEncoder;
use CodeIgniter\Shield\Entities\User;
use stdClass;

/**
 * JWT Manager
 */
class JWTManager
{
    protected Time $clock;
    protected JWSEncoder $jwsEncoder;
    protected JWSDecoder $jwsDecoder;

    public function __construct(
        ?Time $clock = null,
        ?JWSEncoder $jwsEncoder = null,
        ?JWSDecoder $jwsDecoder = null
    ) {
        $this->clock      = $clock ?? new Time();
        $this->jwsEncoder = $jwsEncoder ?? new JWSEncoder(null, $this->clock);
        $this->jwsDecoder = $jwsDecoder ?? new JWSDecoder();
    }

    /**
     * Issues Signed JWT (JWS) for a User
     *
     * @param array                      $claims  The payload items.
     * @param int|null                   $ttl     Time to live in seconds.
     * @param string                     $keyset  The key group.
     *                                            The array key of Config\AuthJWT::$keys.
     * @param array<string, string>|null $headers An array with header elements to attach.
     */
    public function generateToken(
        User $user,
        array $claims = [],
        ?int $ttl = null,
        $keyset = 'default',
        ?array $headers = null
    ): string {
        $payload = array_merge(
            $claims,
            [
                'sub' => (string) $user->id, // subject
            ],
        );

        return $this->issue($payload, $ttl, $keyset, $headers);
    }

    /**
     * Issues Signed JWT (JWS)
     *
     * @param array                      $claims  The payload items.
     * @param int|null                   $ttl     Time to live in seconds.
     * @param string                     $keyset  The key group.
     *                                            The array key of Config\AuthJWT::$keys.
     * @param array<string, string>|null $headers An array with header elements to attach.
     */
    public function issue(
        array $claims,
        ?int $ttl = null,
        $keyset = 'default',
        ?array $headers = null
    ): string {
        return $this->jwsEncoder->encode($claims, $ttl, $keyset, $headers);
    }

    /**
     * Returns payload of the JWT
     *
     * @param string $keyset The key group. The array key of Config\AuthJWT::$keys.
     */
    public function parse(string $encodedToken, $keyset = 'default'): stdClass
    {
        return $this->jwsDecoder->decode($encodedToken, $keyset);
    }
}
