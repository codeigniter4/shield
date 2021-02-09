<?php namespace Sparks\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Class Email2FA
 *
 * Sends an email to the user with a code to verify their account.
 *
 * @package Sparks\Shield\Authentication\Actions
 */
class Email2FA implements ActionInterface
{
	/**
	 * Displays the "Hey we're going to send you an number to your email"
	 * message to the user with a prompt to continue.
	 *
	 * @return mixed
	 */
	public function show()
	{
		echo view(config('Auth')->views['action_email_2fa']);
	}

	/**
	 * Generates the random number, saves it as a temp identity
	 * with the user, and first off an email to the user with the code,
	 * then displays the form to accept the 6 digits
	 *
	 * @param \CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function handle(IncomingRequest $request)
	{
	}

	/**
	 * Attempts to verify the code the user entered.
	 *
	 * @param \CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function verify(IncomingRequest $request)
	{
	}
}
