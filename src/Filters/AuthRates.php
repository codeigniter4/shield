<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Auth Rate-Limiting Filter.
 *
 * Provides rated limiting intended for Auth routes.
 */
class AuthRates implements FilterInterface
{
    /**
     * Intened for use on auth form pages to restrict the number
     * of attempts that can be generated. Restricts it to 10 attempts
     * per minute, which is what auth0 uses.
     *
     * @see https://auth0.com/docs/troubleshoot/customer-support/operational-policies/rate-limit-policy/database-connections-rate-limits
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

        $throttler = service('throttler');

        // Restrict an IP address to no more than 10 requests
        // per minute on any auth-form pages (login, register, forgot, etc).
        if ($throttler->check(md5($request->getIPAddress()), 10, MINUTE, 1) === false) {
            return service('response')->setStatusCode(
                429,
                lang('Auth.throttled', [$throttler->getTokenTime()]) // message
            );
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
        // Nothing required
    }
}
