<?php

namespace CodeIgniter\Shield\Controllers;

use App\Controllers\BaseController;
use CodeIgniter\Exceptions\PageNotFoundException;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Shield\Authentication\Actions\ActionInterface;

/**
 * Class ActionController
 *
 * A generic controller to handle Authentication Actions.
 */
class ActionController extends BaseController
{
    protected ?ActionInterface $action;
    protected $helpers = ['setting'];

    /**
     * Perform an initial check if we have a valid action or not.
     *
     * @param string[] $params
     *
     * @return Response|string
     */
    public function _remap(string $method, ...$params)
    {
        // Grab our action instance if one has been set.
        /** @var class-string<ActionInterface>|null $actionClass */
        $actionClass = session('auth_action');

        if (! empty($actionClass) && class_exists($actionClass)) {
            $this->action = new $actionClass();
        }

        if (empty($this->action) || ! $this->action instanceof ActionInterface) {
            throw new PageNotFoundException();
        }

        return $this->{$method}(...$params);
    }

    /**
     * Shows the initial screen to the user to start the flow.
     * This might be asking for the user's email to reset a password,
     * or asking for a cell-number for a 2FA.
     *
     * @return Response|string
     */
    public function show()
    {
        return $this->action->show();
    }

    /**
     * Processes the form that was displayed in the previous form.
     *
     * @return Response|string
     */
    public function handle()
    {
        return $this->action->handle($this->request);
    }

    /**
     * This handles the response after the user takes action
     * in response to the show/handle flow. This might be
     * from clicking the 'confirm my email' action or
     * following entering a code sent in an SMS.
     *
     * @return Response|string
     */
    public function verify()
    {
        return $this->action->verify($this->request);
    }
}
