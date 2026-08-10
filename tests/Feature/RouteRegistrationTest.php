<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it('registers login, registration, and API routes by default', function (): void {
    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('login')
        ->and($routes)->toHaveKey('login.store')
        ->and($routes)->toHaveKey('register')
        ->and($routes)->toHaveKey('register.store')
        ->and($routes)->toHaveKey('api.login')
        ->and($routes)->toHaveKey('api.register');
});
