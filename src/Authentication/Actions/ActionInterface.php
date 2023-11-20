<?php

declare(strict_types=1);

/**
 * This file is part of CodeIgniter Shield.
 *
 * (c) CodeIgniter Foundation <admin@codeigniter.com>
 *
 * For the full copyright and license information, please view
 * the LICENSE file that was distributed with this source code.
 */

namespace CodeIgniter\Shield\Authentication\Actions;

use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\Response;
use CodeIgniter\Shield\Entities\User;

/**
 * Interface ActionInterface
 *
 * Authentication Actions are steps that can happen after
 * the main authentication steps, like registration and login.
 * They can be email activation steps, SMS-based 2FA, etc.
 */
interface ActionInterface
{
    /**
     * Shows the initial screen to the user to start the flow.
     * This might be asking for the user's email to reset a password,
     * or asking for a cell-number for a 2FA.
     *
     * @return Response|string
     */
    public function show();

    /**
     * Processes the form that was displayed in the previous form.
     *
     * @return Response|string
     */
    public function handle(IncomingRequest $request);

    /**
     * This handles the response after the user takes action
     * in response to the show/handle flow. This might be
     * from clicking the 'confirm my email' action or
     * following entering a code sent in an SMS.
     *
     * @return Response|string
     */
    public function verify(IncomingRequest $request);

    /**
     * Returns the string type of the action class.
     * E.g., 'email_2fa', 'email_activate'.
     */
    public function getType(): string;

    /**
     * Creates an identity for the action of the user.
     *
     * @return string secret
     */
    public function createIdentity(User $user): string;
}
