<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\TokenGenerator;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\Authenticators\JWT\FirebaseAdapter;
use CodeIgniter\Shield\Authentication\Authenticators\JWT\JWTAdapterInterface;
use CodeIgniter\Shield\Entities\User;

class JWTGenerator
{
    private Time $currentTime;
    private JWTAdapterInterface $jwtAdapter;

    public function __construct(?Time $currentTime = null, ?JWTAdapterInterface $jwtAdapter = null)
    {
        $this->currentTime = $currentTime ?? new Time();
        $this->jwtAdapter  = $jwtAdapter ?? new FirebaseAdapter();
    }

    /**
     * Issues JWT Access Token
     */
    public function generateAccessToken(User $user): string
    {
        $config = setting('AuthJWT.config');

        $iat = $this->currentTime->getTimestamp();
        $exp = $iat + $config['timeToLive'];

        $payload = array_merge(
            $config['claims'],
            [
                'sub' => (string) $user->id,    // subject
                'iat' => $iat,                  // issued at
                'exp' => $exp,                  // expiration time
            ]
        );

        return $this->jwtAdapter->generate(
            $payload,
            $config['secretKey'],
            $config['algorithm']
        );
    }

    /**
     * Issues JWT
     *
     * @param array    $claims The payload items.
     * @param int|null $ttl    Time to live in seconds.
     * @param string   $key    The secret key.
     */
    public function generate(array $claims, ?int $ttl = null, $key = null, ?string $algorithm = null): string
    {
        assert(
            (array_key_exists('exp', $claims) && ($ttl !== null)) === false,
            'Cannot pass $claims[\'exp\'] and $ttl at the same time.'
        );

        $config = setting('AuthJWT.config');
        $algorithm ??= $config['algorithm'];
        $key ??= $config['secretKey'];

        $payload = $claims;

        if (! array_key_exists('iat', $claims)) {
            $payload['iat'] = $this->currentTime->getTimestamp();
        }

        if (! array_key_exists('exp', $claims)) {
            $payload['exp'] = $payload['iat'] + $config['timeToLive'];
        }

        if ($ttl !== null) {
            $payload['exp'] = $payload['iat'] + $ttl;
        }

        return $this->jwtAdapter->generate(
            $payload,
            $key,
            $algorithm
        );
    }
}
