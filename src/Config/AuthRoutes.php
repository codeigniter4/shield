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
use CodeIgniter\HTTP\Method;

class AuthRoutes extends BaseConfig
{
    public array $routes = [
        'register' => [
            [
                Method::GET,
                'register',
                'RegisterController::registerView',
                'register', // Route name
            ],
            [
                Method::POST,
                'register',
                'RegisterController::registerAction',
            ],
        ],
        'login' => [
            [
                Method::GET,
                'login',
                'LoginController::loginView',
                'login', // Route name
            ],
            [
                Method::POST,
                'login',
                'LoginController::loginAction',
            ],
        ],
        'magic-link' => [
            [
                Method::GET,
                'login/magic-link',
                'MagicLinkController::loginView',
                'magic-link',        // Route name
            ],
            [
                Method::POST,
                'login/magic-link',
                'MagicLinkController::loginAction',
            ],
            [
                Method::GET,
                'login/verify-magic-link',
                'MagicLinkController::verify',
                'verify-magic-link', // Route name
            ],
        ],
        'logout' => [
            [
                Method::GET,
                'logout',
                'LoginController::logoutAction',
                'logout', // Route name
            ],
        ],
        'auth-actions' => [
            [
                Method::GET,
                'auth/a/show',
                'ActionController::show',
                'auth-action-show', // Route name
            ],
            [
                Method::POST,
                'auth/a/handle',
                'ActionController::handle',
                'auth-action-handle', // Route name
            ],
            [
                Method::POST,
                'auth/a/verify',
                'ActionController::verify',
                'auth-action-verify', // Route name
            ],
        ],
    ];
}
