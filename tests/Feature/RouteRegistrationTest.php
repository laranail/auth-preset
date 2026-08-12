<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;

it('registers login, registration, and API routes when features are enabled', function (): void {
    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('login')
        ->and($routes)->toHaveKey('login.store')
        ->and($routes)->toHaveKey('register')
        ->and($routes)->toHaveKey('register.store')
        ->and($routes)->toHaveKey('api.login')
        ->and($routes)->toHaveKey('api.register');
});

it('registers Fortify password reset routes when feature is enabled', function (): void {
    config()->set('auth-preset.features', array_merge(
        config('auth-preset.features'),
        [Features::passwordReset()]
    ));

    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('password.request')
        ->and($routes)->toHaveKey('password.email')
        ->and($routes)->toHaveKey('password.reset');
});

it('registers Fortify email verification routes when feature is enabled', function (): void {
    config()->set('auth-preset.features', array_merge(
        config('auth-preset.features'),
        [Features::emailVerification()]
    ));

    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('verification.notice')
        ->and($routes)->toHaveKey('verification.verify')
        ->and($routes)->toHaveKey('verification.send');
});
