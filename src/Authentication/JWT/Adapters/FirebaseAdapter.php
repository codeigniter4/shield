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

namespace CodeIgniter\Shield\Authentication\JWT\Adapters;

use CodeIgniter\Shield\Authentication\JWT\Exceptions\InvalidTokenException;
use CodeIgniter\Shield\Authentication\JWT\JWSAdapterInterface;
use CodeIgniter\Shield\Config\AuthJWT;
use CodeIgniter\Shield\Exceptions\InvalidArgumentException as ShieldInvalidArgumentException;
use CodeIgniter\Shield\Exceptions\LogicException as ShieldLogicException;
use DomainException;
use Firebase\JWT\BeforeValidException;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\SignatureInvalidException;
use InvalidArgumentException;
use LogicException;
use stdClass;
use UnexpectedValueException;

class FirebaseAdapter implements JWSAdapterInterface
{
    /**
     * {@inheritDoc}
     */
    public function decode(string $encodedToken, $keyset): stdClass
    {
        try {
            $keys = $this->createKeysForDecode($keyset);

            return JWT::decode($encodedToken, $keys);
        } catch (InvalidArgumentException $e) {
            // provided key/key-array is empty or malformed.
            throw new ShieldInvalidArgumentException(
                'Invalid Keyset: "' . $keyset . '". ' . $e->getMessage(),
                0,
                $e
            );
        } catch (DomainException $e) {
            // provided algorithm is unsupported OR
            // provided key is invalid OR
            // unknown error thrown in openSSL or libsodium OR
            // libsodium is required but not available.
            throw new ShieldLogicException('Cannot decode JWT: ' . $e->getMessage(), 0, $e);
        } catch (SignatureInvalidException $e) {
            // provided JWT signature verification failed.
            throw InvalidTokenException::forInvalidToken($e);
        } catch (BeforeValidException $e) {
            // provided JWT is trying to be used before "nbf" claim OR
            // provided JWT is trying to be used before "iat" claim.
            throw InvalidTokenException::forBeforeValidToken($e);
        } catch (ExpiredException $e) {
            // provided JWT is trying to be used after "exp" claim.
            throw InvalidTokenException::forExpiredToken($e);
        } catch (UnexpectedValueException $e) {
            // provided JWT is malformed OR
            // provided JWT is missing an algorithm / using an unsupported algorithm OR
            // provided JWT algorithm does not match provided key OR
            // provided key ID in key/key-array is empty or invalid.
            log_message(
                'error',
                '[Shield] ' . class_basename($this) . '::' . __FUNCTION__
                . '(' . __LINE__ . ') '
                . get_class($e) . ': ' . $e->getMessage()
            );

            throw InvalidTokenException::forInvalidToken($e);
        }
    }

    /**
     * Creates keys for Decode
     *
     * @param string $keyset
     *
     * @return array|Key key or key array
     */
    private function createKeysForDecode($keyset)
    {
        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        $configKeys = $config->keys[$keyset];

        if (count($configKeys) === 1) {
            $key       = $configKeys[0]['secret'] ?? $configKeys[0]['public'];
            $algorithm = $configKeys[0]['alg'];

            return new Key($key, $algorithm);
        }

        $keys = [];

        foreach ($config->keys[$keyset] as $item) {
            $key       = $item['secret'] ?? $item['public'];
            $algorithm = $item['alg'];

            $keys[$item['kid']] = new Key($key, $algorithm);
        }

        return $keys;
    }

    /**
     * {@inheritDoc}
     */
    public function encode(array $payload, $keyset, ?array $headers = null): string
    {
        try {
            [$key, $keyId, $algorithm] = $this->createKeysForEncode($keyset);

            return JWT::encode($payload, $key, $algorithm, $keyId, $headers);
        } catch (LogicException $e) {
            // errors having to do with environmental setup or malformed JWT Keys
            throw new ShieldLogicException('Cannot encode JWT: ' . $e->getMessage(), 0, $e);
        } catch (UnexpectedValueException $e) {
            // errors having to do with JWT signature and claims
            throw new ShieldLogicException('Cannot encode JWT: ' . $e->getMessage(), 0, $e);
        }
    }

    /**
     * Creates keys for Encode
     *
     * @param string $keyset
     */
    private function createKeysForEncode($keyset): array
    {
        /** @var AuthJWT $config */
        $config = config('AuthJWT');

        if (isset($config->keys[$keyset][0]['secret'])) {
            $key = $config->keys[$keyset][0]['secret'];
        } else {
            $passphrase = $config->keys[$keyset][0]['passphrase'] ?? '';

            if ($passphrase !== '') {
                $key = openssl_pkey_get_private(
                    $config->keys[$keyset][0]['private'],
                    $passphrase
                );
            } else {
                $key = $config->keys[$keyset][0]['private'];
            }
        }

        $algorithm = $config->keys[$keyset][0]['alg'];

        $keyId = $config->keys[$keyset][0]['kid'] ?? null;
        if ($keyId === '') {
            $keyId = null;
        }

        return [$key, $keyId, $algorithm];
    }
}
