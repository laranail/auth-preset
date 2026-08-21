<?php

declare(strict_types=1);

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\AuthPreset\AuthPresetServiceProvider;

it('publishes Blade views for application customization', function (): void {
    $this->artisan('vendor:publish', [
        '--tag' => 'auth-preset-views',
    ])->assertSuccessful();

    expect(resource_path('views/vendor/auth-preset/login.blade.php'))->toBeFile();
});

it('preserves the Auth Preset publish tags and destinations', function (): void {
    $config = ServiceProvider::pathsToPublish(
        provider: AuthPresetServiceProvider::class,
        group: 'auth-preset-config',
    );
    $routes = ServiceProvider::pathsToPublish(
        provider: AuthPresetServiceProvider::class,
        group: 'auth-preset-routes',
    );
    $views = ServiceProvider::pathsToPublish(
        provider: AuthPresetServiceProvider::class,
        group: 'auth-preset-views',
    );

    expect(array_values($config))->toContain(config_path('auth-preset.php'))
        ->and(array_values($routes))->toContain(base_path('routes/auth-preset-web.php'))
        ->and(array_values($routes))->toContain(base_path('routes/auth-preset-api.php'))
        ->and(array_values($views))->toContain(resource_path('views/vendor/auth-preset'));
});
