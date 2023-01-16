<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\Session;

/**
 * Force Password Reset Filter.
 */
class ForcePasswordResetFilter implements FilterInterface
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
     * @param array|null $arguments
     *
     * @return RedirectResponse|void
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        if (! $request instanceof IncomingRequest) {
            return;
        }

        helper('setting');

        /** @var Session $authenticator */
        $authenticator = auth('session')->getAuthenticator();

        if ($authenticator->loggedIn()) {
            if (auth()->user()->requiresPasswordReset()) {
                return redirect()->to(config('Auth')->forcePasswordResetRedirect());
            }
        }
    }

    /**
     * We don't have anything to do here.
     *
     * @param Response|ResponseInterface $response
     * @param array|null                 $arguments
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null): void
    {
    }
}
