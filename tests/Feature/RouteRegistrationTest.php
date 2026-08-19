<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use Simtabi\Laranail\AuthPreset\Features;

it(description: 'registers the dashboard route', closure: function (): void {
    $route = Route::getRoutes()->getByName('dashboard');

    expect($route)->not->toBeNull()
        ->and($route->uri())->toBe('dashboard')
        ->and($route->gatherMiddleware())->toContain('auth:' . config('auth-preset.guard'));
});

it(description: 'registers login, registration, and API routes when features are enabled', closure: function (): void {
    $routes = Route::getRoutes()->getRoutesByName();

    expect(value: $routes)->toHaveKey(key: 'login')
        ->and(value: $routes)->toHaveKey(key: 'login.store')
        ->and(value: $routes)->toHaveKey(key: 'register')
        ->and(value: $routes)->toHaveKey(key: 'register.store')
        ->and(value: $routes)->toHaveKey(key: 'api.login')
        ->and(value: $routes)->toHaveKey(key: 'api.register');
});

it(description: 'registers Fortify password reset routes when feature is enabled', closure: function (): void {
    config()->set(key: 'auth-preset.features', value: array_merge(
        config(key: 'auth-preset.features'),
        [Features::passwordReset()]
    ));

    $routes = Route::getRoutes()->getRoutesByName();

    expect(value: $routes)->toHaveKey(key: 'password.request')
        ->and(value: $routes)->toHaveKey(key: 'password.email')
        ->and(value: $routes)->toHaveKey(key: 'password.reset');
});

it(description: 'registers Fortify email verification routes when feature is enabled', closure: function (): void {
    config()->set(key: 'auth-preset.features', value: array_merge(
        config(key: 'auth-preset.features'),
        [Features::emailVerification()]
    ));

    $routes = Route::getRoutes()->getRoutesByName();

    expect(value: $routes)->toHaveKey(key: 'verification.notice')
        ->and(value: $routes)->toHaveKey(key: 'verification.verify')
        ->and(value: $routes)->toHaveKey(key: 'verification.send');
});

it(description: 'registers password update routes when feature is enabled', closure: function (): void {
    $routes = Route::getRoutes()->getRoutesByName();

    expect(value: $routes)->toHaveKey(key: 'user-password.edit')
        ->and(value: $routes)->toHaveKey(key: 'user-password.update')
        ->and(value: $routes)->toHaveKey(key: 'api.user-password.update');
});

it(description: 'registers profile information update routes when feature is enabled', closure: function (): void {
    $routes = Route::getRoutes()->getRoutesByName();

    expect(value: $routes)->toHaveKey(key: 'user-profile-information.edit')
        ->and(value: $routes)->toHaveKey(key: 'user-profile-information.update')
        ->and(value: $routes)->toHaveKey(key: 'api.user-profile-information.update');
});

it(description: 'registers the passkey management page and Fortify passkey routes', closure: function (): void {
    $routes = Route::getRoutes()->getRoutesByName();

    expect(value: $routes)->toHaveKey(key: 'user-passkeys.index')
        ->and(value: $routes)->toHaveKey(key: 'passkey.login-options')
        ->and(value: $routes)->toHaveKey(key: 'passkey.login')
        ->and(value: $routes)->toHaveKey(key: 'passkey.confirm-options')
        ->and(value: $routes)->toHaveKey(key: 'passkey.confirm')
        ->and(value: $routes)->toHaveKey(key: 'passkey.registration-options')
        ->and(value: $routes)->toHaveKey(key: 'passkey.store')
        ->and(value: $routes)->toHaveKey(key: 'passkey.destroy')
        ->and(value: $routes)->not->toHaveKey(key: 'api.passkey.login');
});
