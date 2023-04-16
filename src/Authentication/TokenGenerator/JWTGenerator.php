<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\TokenGenerator;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT\FirebaseAdapter;
use CodeIgniter\Shield\Authentication\Authenticators\JWT\JWTAdapterInterface;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Entities\User;

class JWTGenerator
{
    private Time $clock;
    private JWTAdapterInterface $jwtAdapter;

    public function __construct(?Time $clock = null, ?JWTAdapterInterface $jwtAdapter = null)
    {
        $this->clock      = $clock ?? new Time();
        $this->jwtAdapter = $jwtAdapter ?? new FirebaseAdapter();
    }

    /**
     * Issues JWT (JWS) for a User
     */
    public function generateAccessToken(User $user): string
    {
        $config = config(AuthJWT::class);

        $iat = $this->clock->now()->getTimestamp();
        $exp = $iat + $config->timeToLive;

        $payload = array_merge(
            $config->defaultClaims,
            [
                'sub' => (string) $user->id,    // subject
                'iat' => $iat,                  // issued at
                'exp' => $exp,                  // expiration time
            ]
        );

        return $this->jwtAdapter->generate(
            $payload,
            $config->keys['default'][0]['secret'],
            $config->keys['default'][0]['alg']
        );
    }

    /**
     * Issues JWT (JWS)
     *
     * @param array                      $claims  The payload items.
     * @param int|null                   $ttl     Time to live in seconds.
     * @param string                     $key     The key group.
     *                                            The array key of Config\AuthJWT::$keys.
     * @param array<string, string>|null $headers An array with header elements to attach.
     */
    public function generate(
        array $claims,
        ?int $ttl = null,
        $key = 'default',
        ?array $headers = null
    ): string {
        assert(
            (array_key_exists('exp', $claims) && ($ttl !== null)) === false,
            'Cannot pass $claims[\'exp\'] and $ttl at the same time.'
        );

        $keyGroup = $key;

        $config    = config(AuthJWT::class);
        $key       = $config->keys[$keyGroup][0]['secret'];
        $algorithm = $config->keys[$keyGroup][0]['alg'];

        $keyId = $config->keys[$keyGroup][0]['kid'] ?? null;
        if ($keyId === '') {
            $keyId = null;
        }

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

        return $this->jwtAdapter->generate(
            $payload,
            $key,
            $algorithm,
            $keyId,
            $headers
        );
    }
}
