<?php

namespace Sparks\Shield\Config;

use CodeIgniter\Config\BaseConfig;

class AuthRoutes extends BaseConfig
{
    public $routes = [
        'register' => [
            [
                'get',
                'register',
                'RegisterController::registerView',
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
            ],
            [
                'post',
                'login',
                'LoginController::loginAction',
            ],
        ],
        'logout' => [
            [
                'get',
                'logout',
                'LoginController::logoutAction',
            ],
        ],
        'auth-actions' => [
            [
                'get',
                'auth/a/show',
                'ActionController::show',
            ],
            [
                'post',
                'auth/a/handle',
                'ActionController::handle',
            ],
            [
                'post',
                'auth/a/verify',
                'ActionController::verify',
            ],
        ],
    ];
}
