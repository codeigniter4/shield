<?php

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

use CodeIgniter\Shield\Exceptions\GroupException;

class GroupFilter implements FilterInterface
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
     * @param array|null $params
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $params = null)
    {
        if (empty($params)) {
            return;
        }

        if (! function_exists('auth')) {
            helper('auth');
        }

        if (! auth()->loggedIn()) {
            return redirect()->to('login');
        }

        foreach ($params as $group) {
            if (auth()->user()->inGroup($group)) {
                return;
            }
        }

        throw new GroupException(lang('Auth.notEnoughPrivilege'));
    }

    /**
     * We don't have anything to do here.
     *
     * @param Response|ResponseInterface $response
     * @param array|null                 $arguments
     *
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Nothing required
    }
}
