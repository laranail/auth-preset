<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;

it(description: 'registers web login route when email_login enabled', closure: function (): void {
    $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('login');
});

it(description: 'registers web username login route when username_login enabled', closure: function (): void {
    $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('username.login');
});

it(description: 'registers check email route', closure: function (): void {
    $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('check.email');
});

it(description: 'registers check username route', closure: function (): void {
    $this->app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

    $routes = Route::getRoutes()->getRoutesByName();

    expect($routes)->toHaveKey('check.username');
});
