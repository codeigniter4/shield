<?php

namespace Sparks\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use Sparks\Shield\Entities\UserIdentity;
use Sparks\Shield\Models\UserIdentityModel;

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
        $identityModel = model(UserIdentityModel::class);
        $identities    = $identityModel
            ->where('user_id', auth('session')->id())
            ->whereIn('type', ['email_2fa', 'email_activate'])
            ->findAll();

        foreach ($identities as $identity) {
            if (! $identity instanceof UserIdentity) {
                continue;
            }

            $action = setting('Auth.actions')[$identity->name];

            if ($action) {
                session()->set('auth_action', $action);

                return redirect()->route('auth-action-show')->with('error', $identity->extra);
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
