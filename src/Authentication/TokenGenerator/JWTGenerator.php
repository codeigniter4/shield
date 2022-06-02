<?php

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
        $config = setting('Auth.jwtConfig');

        $iat = $this->currentTime->getTimestamp();
        $exp = $iat + $config['timeToLive'];

        $payload = [
            'iss' => $config['issuer'],     // issuer
            'aud' => $config['audience'],   // audience
            'sub' => (string) $user->id,    // subject
            'iat' => $iat,                  // issued at
            'exp' => $exp,                  // expiration time
        ];

        return $this->jwtAdapter->generate(
            $payload,
            $config['secretKey'],
            $config['algorithm']
        );
    }

    /**
     * Issues JWT
     *
     * @param int|null $ttl Time to live in seconds.
     * @param string   $key The secret key.
     */
    public function generate(array $payload, ?int $ttl = null, $key = null, ?string $algorithm = null): string
    {
        assert(
            (array_key_exists('exp', $payload) && ($ttl !== null)) === false,
            'Cannot pass $payload[\'exp\'] and $ttl at the same time.'
        );

        $config = setting('Auth.jwtConfig');
        $algorithm ??= $config['algorithm'];
        $key ??= $config['secretKey'];

        if (! array_key_exists('iat', $payload)) {
            $payload['iat'] = $this->currentTime->getTimestamp();
        }

        if (! array_key_exists('exp', $payload)) {
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
