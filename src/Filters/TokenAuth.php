<?php

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Security\Exceptions\SecurityException;
use Config\Services;

/**
 * Access Token Authentication Filter.
 *
 * Personal Access Token authentication for web applications.
 */
class TokenAuth implements FilterInterface
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
	 * @param RequestInterface|IncomingRequest $request
	 * @param array|null                       $arguments
	 *
	 * @return mixed
	 */
	public function before(RequestInterface $request, $arguments = null)
	{
		helper('auth');

		$result = auth('tokens')->authenticate([
			'token' => $request->getHeaderLine('Authorization'),
		]);

		if (! $result->isOK())
		{
			return redirect()->to('/login');
		}
	}

	/**
	 * We don't have anything to do here.
	 *
	 * @param RequestInterface|IncomingRequest             $request
	 * @param ResponseInterface|\CodeIgniter\HTTP\Response $response
	 * @param array|null                                   $arguments
	 *
	 * @return mixed
	 */
	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}
}
