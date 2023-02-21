<?php namespace Sparks\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;

/**
 * Interface ActionInterface
 *
 * Authentication Actions are steps that can happen after
 * the main authentication steps, like registration and login.
 * They can be email activation steps, SMS-based 2FA, etc.
 *
 * @package Sparks\Shield\Authentication\Actions
 */
interface ActionInterface
{
	/**
	 * Shows the initial screen to the user to start the flow.
	 * This might be asking for the user's email to reset a password,
	 * or asking for a cell-number for a 2FA.
	 *
	 * @return mixed
	 */
	public function show();

	/**
	 * Processes the form that was displayed in the previous form.
	 *
	 * @param \CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function handle(IncomingRequest $request);

	/**
	 * This handles the response after the user takes action
	 * in response to the show/handle flow. This might be
	 * from clicking the 'confirm my email' action or
	 * following entering a code sent in an SMS.
	 *
	 * @param \CodeIgniter\HTTP\IncomingRequest $request
	 *
	 * @return mixed
	 */
	public function verify(IncomingRequest $request);
}
