<?php

namespace Sparks\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RequestInterface;
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
     * @param IncomingRequest|RequestInterface $request
     * @param array|null                       $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper('auth');

        $chain = config('Auth')->authenticationChain;

        foreach ($chain as $handler) {
            if (auth($handler)->loggedIn()) {
                // Make sure Auth uses this handler
                auth()->setHandler($handler);

                return;
            }
        }

        return redirect()->route('login');
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
        // Nothing required
    }
}
