<?php

namespace CodeIgniter\Shield\Authentication\TokenGenerator;

use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWT\Firebase;
use CodeIgniter\Shield\Authentication\TokenGenerator\JWT\JWTGeneratorInterface;
use CodeIgniter\Shield\Entities\User;

class JWTGenerator
{
    private Time $currentTime;
    private JWTGeneratorInterface $jwtGenerator;

    public function __construct(?Time $currentTime = null, ?JWTGeneratorInterface $jwtGenerator = null)
    {
        $this->currentTime  = $currentTime ?? new Time();
        $this->jwtGenerator = $jwtGenerator ?? new Firebase();
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

        return $this->jwtGenerator->generate($payload, $config['secretKey'], $config['algorithm']);
    }
}
