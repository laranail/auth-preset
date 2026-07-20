<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Frontend Stack
    |--------------------------------------------------------------------------
    |
    | The frontend stack to scaffold. Options: 'blade', 'livewire',
    | 'inertia-vue', 'inertia-react'.
    |
    */

    'stack' => env(key: 'AUTH_PRESET_STACK', default: 'blade'),

    /*
    |--------------------------------------------------------------------------
    | Features
    |--------------------------------------------------------------------------
    |
    | Toggle authentication features on or off. When a feature is disabled,
    | its corresponding routes and UI elements will not be registered.
    |
    */

    'features' => [
        'email_login'    => (bool) env(key: 'AUTH_PRESET_EMAIL_LOGIN', default: true),
        'username_login' => (bool) env(key: 'AUTH_PRESET_USERNAME_LOGIN', default: true),
        'api_routes'     => (bool) env(key: 'AUTH_PRESET_API_ROUTES', default: true),
        'web_routes'     => (bool) env(key: 'AUTH_PRESET_WEB_ROUTES', default: true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Prefixes
    |--------------------------------------------------------------------------
    */

    'prefix' => [
        'web' => env(key: 'AUTH_PRESET_WEB_PREFIX', default: 'auth'),
        'api' => env(key: 'AUTH_PRESET_API_PREFIX', default: 'api/auth'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Route Middleware
    |--------------------------------------------------------------------------
    */

    'middleware' => [
        'web' => ['web', 'guest'],
        'api' => ['api', 'guest', 'throttle:60,1'],
    ],

    /*
    |--------------------------------------------------------------------------
    | Redirects
    |--------------------------------------------------------------------------
    |
    | Where to redirect users after a successful login.
    |
    */

    'redirects' => [
        'after_login' => env(key: 'AUTH_PRESET_AFTER_LOGIN', default: '/dashboard'),
    ],

];
