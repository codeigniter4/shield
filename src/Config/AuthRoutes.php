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

namespace CodeIgniter\Shield\Config;

use CodeIgniter\Config\BaseConfig;

class AuthRoutes extends BaseConfig
{
    public array $routes = [
        'register' => [
            [
                'GET',
                'register',
                'RegisterController::registerView',
                'register', // Route name
            ],
            [
                'POST',
                'register',
                'RegisterController::registerAction',
            ],
        ],
        'login' => [
            [
                'GET',
                'login',
                'LoginController::loginView',
                'login', // Route name
            ],
            [
                'POST',
                'login',
                'LoginController::loginAction',
            ],
        ],
        'magic-link' => [
            [
                'GET',
                'login/magic-link',
                'MagicLinkController::loginView',
                'magic-link',        // Route name
            ],
            [
                'POST',
                'login/magic-link',
                'MagicLinkController::loginAction',
            ],
            [
                'GET',
                'login/verify-magic-link',
                'MagicLinkController::verify',
                'verify-magic-link', // Route name
            ],
        ],
        'logout' => [
            [
                'GET',
                'logout',
                'LoginController::logoutAction',
                'logout', // Route name
            ],
        ],
        'auth-actions' => [
            [
                'GET',
                'auth/a/show',
                'ActionController::show',
                'auth-action-show', // Route name
            ],
            [
                'POST',
                'auth/a/handle',
                'ActionController::handle',
                'auth-action-handle', // Route name
            ],
            [
                'POST',
                'auth/a/verify',
                'ActionController::verify',
                'auth-action-verify', // Route name
            ],
        ],
    ];
}
