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

namespace CodeIgniter\Shield\Authentication\JWT;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\JWT\Adapters\FirebaseAdapter;
use CodeIgniter\Shield\Config\AuthJWT;

class JWSEncoder
{
    protected Time $clock;
    protected JWSAdapterInterface $jwsAdapter;

    public function __construct(?JWSAdapterInterface $jwsAdapter = null, ?Time $clock = null)
    {
        $this->jwsAdapter = $jwsAdapter ?? new FirebaseAdapter();
        $this->clock      = $clock ?? new Time();
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
    public function encode(
        array $claims,
        ?int $ttl = null,
        $keyset = 'default',
        ?array $headers = null
    ): string {
        assert(
            (array_key_exists('exp', $claims) && ($ttl !== null)) === false,
            'Cannot pass $claims[\'exp\'] and $ttl at the same time.'
        );

        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        $payload = array_merge(
            $config->defaultClaims,
            $claims
        );

        if (! array_key_exists('iat', $claims)) {
            $payload['iat'] = $this->clock->now()->getTimestamp();
        }

        if (! array_key_exists('exp', $claims)) {
            $payload['exp'] = $payload['iat'] + $config->timeToLive;
        }

        if ($ttl !== null) {
            $payload['exp'] = $payload['iat'] + $ttl;
        }

        return $this->jwsAdapter->encode(
            $payload,
            $keyset,
            $headers
        );
    }
}
