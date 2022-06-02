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
}
