<?php

namespace CodeIgniter\Shield\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RedirectResponse;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseInterface;

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
        helper(['auth', 'setting']);

        $result = auth('tokens')->authenticate([
            'token' => $request->getHeaderLine(setting('Auth.authenticatorHeader')['tokens'] ?? 'Authorization'),
        ]);

        if (! $result->isOK()) {
            return redirect()->to('/login');
        }

        if (setting('Auth.recordActiveDate')) {
            auth('tokens')->recordActiveDate();
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
