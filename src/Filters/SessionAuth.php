<?php

namespace Sparks\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Sparks\Shield\Entities\UserIdentity;

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
     * @param array|null $arguments
     *
     * @return mixed
     */
    public function before(RequestInterface $request, $arguments = null)
    {
        helper(['auth', 'setting']);

        if (! auth('session')->loggedIn()) {
            return redirect()->route('login');
        }

        if (setting('Auth.recordActiveDate')) {
            auth('session')->recordActive();
        }

        // If user is in middle of an action flow
        // ensure they must finish it first.
        $user     = auth('session')->user();
        $identity = auth('session')->user()->getIdentity('email_2fa');
        if ($identity instanceof UserIdentity) {
            $action = config('Auth')->actions['login'];

            if ($action) {
                session()->set('auth_action', $action);

                return redirect()->route('auth-action-show')->with('error', lang('Auth.need2FA'));
            }
        }
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
    }
}
