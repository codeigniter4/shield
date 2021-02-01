<?php

namespace Sparks\Shield\Controllers;

use CodeIgniter\Controller;
use Sparks\Shield\Entities\User;

/**
 * Class RegisterController
 *
 * Handles displaying registration form,
 * and handling actual registration flow.
 *
 * @package Sparks\Shield\Controllers
 */
class RegisterController extends Controller
{
	/**
	 * @var \Sparks\Shield\Config\Auth
	 */
	protected $config;

	/**
	 * Default URI to redirect to after
	 * successfully registering.
	 *
	 * @var string
	 */
	protected $redirectURL = '/';

	public function __construct()
	{
		$this->config = config('Auth');
	}

	/**
	 * Displays the registration form.
	 */
	public function registerView()
	{
		// Check if registration is allowed
		if (! $this->config->allowRegistration)
		{
			return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
		}

		echo view(config('Auth')->views['register']);
	}

	/**
	 * Attempts to register the user.
	 */
	public function registerAction()
	{
		// Check if registration is allowed
		if (! $this->config->allowRegistration)
		{
			return redirect()->back()->withInput()->with('error', lang('Auth.registerDisabled'));
		}

		$users = $this->getUserProvider();

		// Validate here first, since some things,
		// like the password, can only be validated properly here.
		$rules = $this->getValidationRules();

		if (! $this->validate($rules))
		{
			return redirect()->back()->withInput()->with('errors', service('validation')->getErrors());
		}

		// Save the user
		$allowedPostFields = array_merge($this->config->validFields, $this->config->personalFields);
		$user              = $this->getUserEntity();

		$user->fill($this->request->getPost($allowedPostFields));

		if (! $users->save($user))
		{
			return redirect()->back()->withInput()->with('errors', $users->errors());
		}

		// Get the updated user so we have the ID...
		$user = $users->find($users->getInsertID());

		// Store the email/password identity for this user.
		$user->createEmailIdentity($this->request->getPost(['email', 'password']));

		// Success!
		$redirectURL = $this->getRedirectURL();
		return redirect()->to($redirectURL)->with('message', lang('Auth.registerSuccess'));
	}

	/**
	 * Returns the User provider
	 *
	 * @return mixed
	 */
	protected function getUserProvider()
	{
		return model('UserModel');
	}

	/**
	 * Returns the Entity class that should be used
	 *
	 * @return \Sparks\Shield\Entities\User
	 */
	protected function getUserEntity()
	{
		return new User();
	}

	/**
	 * Returns the rules that should be used for validation.
	 *
	 * @return string[]
	 */
	protected function getValidationRules()
	{
		return [
			'username'     => 'required|alpha_numeric_space|min_length[3]|is_unique[users.username]',
			'email'        => 'required|valid_email|is_unique[auth_identities.secret]',
			'password'     => 'required|strong_password',
			'pass_confirm' => 'required|matches[password]',
		];
	}

	/**
	 * Returns the URL the user should be redirected to
	 * after a successful registration.
	 *
	 * @return string
	 */
	protected function getRedirectURL()
	{
		return strpos($this->redirectURL, 'http') === 0
			? $this->redirectURL
			: rtrim(site_url($this->redirectURL), '/ ');
	}
}
