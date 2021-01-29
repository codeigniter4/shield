<?php

namespace Sparks\Shield\Authentication\Passwords;

use CodeIgniter\Entity;
use CodeIgniter\Config\Services;
use CodeIgniter\HTTP\Exceptions\HTTPException;
use Sparks\Shield\Authentication\AuthenticationException;
use Sparks\Shield\Result;

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
 *
 * @package Sparks\Shield\Authentication\Passwords\Validators
 */
class PwnedValidator extends BaseValidator implements ValidatorInterface
{

	/**
	 * Error message.
	 *
	 * @var string
	 */
	protected $error;

	/**
	 * Suggestion message.
	 *
	 * @var string
	 */
	protected $suggestion;

	/**
	 * Checks the password against the online database and
	 * returns false if a match is found. Returns true if no match is found.
	 * If true is returned the password will be passed to next validator.
	 * If false is returned the validation process will be immediately stopped.
	 *
	 * @param string                   $password
	 * @param \CodeIgniter\Entity|null $user
	 *
	 * @return \Sparks\Shield\Result
	 * @throws \Sparks\Shield\Authentication\AuthenticationException
	 */
	public function check(string $password, Entity $user = null): Result
	{
		$hashedPword = strtoupper(sha1($password));
		$rangeHash   = substr($hashedPword, 0, 5);
		$searchHash  = substr($hashedPword, 5);

		try
		{
			$client = Services::curlrequest([
				'base_uri' => 'https://api.pwnedpasswords.com/',
			]);

			$response = $client->get('range/' . $rangeHash,
				['headers' => ['Accept' => 'text/plain']]
			);
		}
		catch (HTTPException $e)
		{
			$exception = AuthenticationException::forHIBPCurlFail($e);
			log_message('error', '[ERROR] {exception}', ['exception' => $exception]);
			throw $exception;
		}

		$range    = $response->getBody();
		$startPos = strpos($range, $searchHash);
		if ($startPos === false)
		{
			return new Result([
				'success' => true,
			]);
		}

		$startPos += 36; // right after the delimiter (:)
		$endPos    = strpos($range, "\r\n", $startPos);
		if ($endPos !== false)
		{
			$hits = (int) substr($range, $startPos, $endPos - $startPos);
		}
		else
		{
			// match is the last item in the range which does not end with "\r\n"
			$hits = (int) substr($range, $startPos);
		}

		$wording = $hits > 1 ? 'databases' : 'a database';

		return new Result([
			'success'   => false,
			'reason'    => lang('Auth.errorPasswordPwned', [$password, $hits, $wording]),
			'extraInfo' => lang('Auth.suggestPasswordPwned', [$password]),
		]);
	}

}
