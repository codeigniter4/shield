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
     * Checks if a logged in user should reset their
     * password, and then redirect to the appropriate
     * page.
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

        if ($authenticator->loggedIn() && $authenticator->getUser()->requiresPasswordReset()) {
            return redirect()->to(config('Auth')->forcePasswordResetRedirect());
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
