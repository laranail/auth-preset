<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\LoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\UsernameLoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\CheckEmailExistsController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Api\CheckUsernameExistsController;

Route::prefix(config(key: 'auth-preset.prefix.api', default: 'api/auth'))
    ->middleware(config(key: 'auth-preset.middleware.api', default: ['api', 'guest', 'throttle:60,1']))
    ->group(function (): void {
        $features = config(key: 'auth-preset.features', default: []);

        if ($features['email_login'] ?? true) {
            Route::post(uri: '/login', action: LoginController::class)
                ->name('api.login');
            Route::post(uri: '/check-email', action: CheckEmailExistsController::class)
                ->name('api.check.email');
        }

        if ($features['username_login'] ?? true) {
            Route::post(uri: '/username/login', action: UsernameLoginController::class)
                ->name('api.username.login');
            Route::post(uri: '/check-username', action: CheckUsernameExistsController::class)
                ->name('api.check.username');
        }
    });
