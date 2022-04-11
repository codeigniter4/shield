<?php

namespace Sparks\Shield\Config;

use CodeIgniter\Config\BaseConfig;

class AuthRoutes extends BaseConfig
{
    public array $routes = [
        'register' => [
            [
                'get',
                'register',
                'RegisterController::registerView',
                'auth-register',
            ],
            [
                'post',
                'register',
                'RegisterController::registerAction',
            ],
        ],
        'login' => [
            [
                'get',
                'login',
                'LoginController::loginView',
                'auth-login',
            ],
            [
                'post',
                'login',
                'LoginController::loginAction',
            ],
        ],
        'magic-link' => [
            [
                'get',
                'login/magic-link',
                'MagicLinkController::loginView',
                'magic-link',        // Route name
            ],
            [
                'post',
                'login/magic-link',
                'MagicLinkController::loginAction',
            ],
            [
                'get',
                'login/verify-magic-link',
                'MagicLinkController::verify',
                'verify-magic-link', // Route name
            ],
        ],
        'logout' => [
            [
                'get',
                'logout',
                'LoginController::logoutAction',
                'auth-logout',
            ],
        ],
        'auth-actions' => [
            [
                'get',
                'auth/a/show',
                'ActionController::show',
                'auth-action-show',
            ],
            [
                'post',
                'auth/a/handle',
                'ActionController::handle',
                'auth-action-handle',
            ],
            [
                'post',
                'auth/a/verify',
                'ActionController::verify',
                'auth-action-verify',
            ],
        ],
    ];
}
