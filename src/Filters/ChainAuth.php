<?php

namespace Sparks\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

/**
 * Chain Authentication Filter.
 *
 * Checks all authentication systems specified within
 * `Config\Auth->authenticationChain`
 */
class ChainAuth implements FilterInterface
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
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['auth', 'setting']);

        $chain = setting('Auth.authenticationChain');

        foreach ($chain as $handler) {
            if (auth($handler)->loggedIn()) {
                // If Auth login is defined and user is not 2FA logged.
                if (! empty(setting('Auth.actions')['login']) && ! auth($handler)->logged2Fa()) {
                    // Make sure route is on available list;
                    foreach (setting('Auth.actionAcceptedRoutes')['login'] as $value) {
                        if ('/' . $request->getUri()->getPath() === route_to($value)) {
                            // Make sure Auth uses this handler
                            auth()->setHandler($handler);

                            return;
                        }
                    }
                    // In other case redirect to 2FA request page.
                    return redirect()->route('auth-login');
                }

                // Make sure Auth uses this handler / No need to check 2FA.
                auth()->setHandler($handler);

                return;
            }
        }

        return redirect()->route('auth-login');
    }

    /**
     * We don't have anything to do here.
     *
     * @param Response|ResponseInterface $response
     * @param array|null                 $arguments
     *
     * @return mixed
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing required
    }
}
