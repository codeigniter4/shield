<?php

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Entities\UserIdentity;
use CodeIgniter\Shield\Models\UserIdentityModel;

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
     * @return RedirectResponse|void
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

        /** @var UserIdentityModel $identityModel */
        $identityModel = model(UserIdentityModel::class);

        // If user is in middle of an action flow
        // ensure they must finish it first.
        $identities = $identityModel->getIdentitiesByTypes(
            auth('session')->id(),
            ['email_2fa', 'email_activate']
        );

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
     * @return void
     */
    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
