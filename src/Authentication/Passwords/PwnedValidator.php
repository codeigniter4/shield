<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Authentication\Passwords;

use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use CodeIgniter\Shield\Authentication\AuthenticationException;
use CodeIgniter\Shield\Entities\User;
use CodeIgniter\Shield\Result;

/**
 * Class PwnedValidator
 *
 * Checks if the password has been compromised by checking against
 * an online database of over 555 million stolen passwords.
 *
 * @see https://www.troyhunt.com/ive-just-launched-pwned-passwords-version-2/
 *
 * NIST recommend to check passwords against those obtained from previous data breaches.
 * @see https://pages.nist.gov/800-63-3/sp800-63b.html#sec5
 */
class PwnedValidator extends BaseValidator implements ValidatorInterface
{
    /**
     * Checks the password against the online database and
     * returns false if a match is found. Returns true if no match is found.
     * If true is returned the password will be passed to next validator.
     * If false is returned the validation process will be immediately stopped.
     *
     * @throws AuthenticationException
     */
    public function check(string $password, ?User $user = null): Result
    {
        $hashedPword = strtoupper(sha1($password));
        $rangeHash   = substr($hashedPword, 0, 5);
        $searchHash  = substr($hashedPword, 5);

        try {
            $client = Services::curlrequest([
                'base_uri' => 'https://api.pwnedpasswords.com/',
            ]);

            $response = $client->get(
                'range/' . $rangeHash,
                ['headers' => ['Accept' => 'text/plain']]
            );
        } catch (HTTPException $e) {
            $exception = AuthenticationException::forHIBPCurlFail($e);
            log_message('error', '[ERROR] {exception}', ['exception' => $exception]);

            throw $exception;
        }

        $range    = $response->getBody();
        $startPos = strpos($range, $searchHash);
        if ($startPos === false) {
            return new Result([
                'success' => true,
            ]);
        }

        $startPos += 36; // right after the delimiter (:)
        $endPos = strpos($range, "\r\n", $startPos);
        $hits   = $endPos !== false ? (int) substr($range, $startPos, $endPos - $startPos) : (int) substr($range, $startPos);

        $wording = $hits > 1 ? 'databases' : 'a database';

        return new Result([
            'success'   => false,
            'reason'    => lang('Auth.errorPasswordPwned', [$password, $hits, $wording]),
            'extraInfo' => lang('Auth.suggestPasswordPwned', [$password]),
        ]);
    }
}
