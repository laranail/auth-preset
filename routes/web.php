<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\LoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\UsernameLoginController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\CheckEmailExistsController;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\CheckUsernameExistsController;

$features = config(key: 'auth-preset.features', default: []);

Route::prefix(config(key: 'auth-preset.prefix.web', default: 'auth'))
    ->middleware(config(key: 'auth-preset.middleware.web', default: ['web', 'guest']))
    ->group(function () use ($features): void {
        if ($features['email_login'] ?? true) {
            Route::get(uri: '/login', action: LoginController::class)
                ->name('login');
            Route::post(uri: '/login', action: LoginController::class);
        }

        if ($features['username_login'] ?? true) {
            Route::get(uri: '/username/login', action: UsernameLoginController::class)
                ->name('username.login');
            Route::post(uri: '/username/login', action: UsernameLoginController::class);
        }

        if ($features['email_login'] ?? true) {
            Route::post(uri: '/check-email', action: CheckEmailExistsController::class)
                ->name('check.email');
        }

        if ($features['username_login'] ?? true) {
            Route::post(uri: '/check-username', action: CheckUsernameExistsController::class)
                ->name('check.username');
        }
    });
