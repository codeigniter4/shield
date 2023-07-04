<?php

declare(strict_types=1);

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Shield\Authentication\Authenticators\AccessTokens;

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

        /** @var AccessTokens $authenticator */
        $authenticator = auth('tokens')->getAuthenticator();

        $result = $authenticator->attempt([
            'token' => $request->getHeaderLine(setting('Auth.authenticatorHeader')['tokens'] ?? 'Authorization'),
        ]);

        if (! $result->isOK() || (! empty($arguments) && $result->extraInfo()->tokenCant($arguments[0]))) {
            return service('response')
                ->setStatusCode(Response::HTTP_UNAUTHORIZED)
                ->setJson(['message' => lang('Auth.badToken')]);
        }

        if (setting('Auth.recordActiveDate')) {
            $authenticator->recordActiveDate();
        }

        // Block inactive users when Email Activation is enabled
        $user = $authenticator->getUser();
        if ($user !== null && ! $user->isActivated()) {
            $authenticator->logout();

            return service('response')
                ->setStatusCode(Response::HTTP_FORBIDDEN)
                ->setJson(['message' => lang('Auth.activationBlocked')]);
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
