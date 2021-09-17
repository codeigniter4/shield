<?php

namespace Sparks\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Session Authentication Filter.
 *
 * Email/Password-based authentication for web applications.
 */
class SessionAuth implements FilterInterface
{
	/**
	 * Do whatever processing this filter needs to do.
	 * By default it should not return anything during
	 * normal execution. However, when an abnormal state
	 * is found, it should return an instance of
	 * CodeIgniter\HTTP\Response. If it does, script
	 * execution will end and that Response will be
	 * sent back to the client, allowing for error pages,
	 * redirects, etc.
	 *
	 * @param IncomingRequest|RequestInterface $request
	 * @param array|null                       $arguments
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request, $arguments = null)
	{
		helper(['auth', 'setting']);

		if (! auth('session')->loggedIn())
		{
			return redirect()->to('/login');
		}

		if (setting('Auth.recordActiveDate'))
		{
			auth('session')->recordActive();
		}
	}

	/**
	 * We don't have anything to do here.
	 *
	 * @param IncomingRequest|RequestInterface             $request
	 * @param \CodeIgniter\HTTP\Response|ResponseInterface $response
	 * @param array|null                                   $arguments
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}
}
