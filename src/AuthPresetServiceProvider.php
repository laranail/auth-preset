<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\AuthPreset\Commands\InstallCommand;

class AuthPresetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/auth-preset.php', 'auth-preset');
    }

    public function boot(): void
    {
        $this->registerPublishes();
        $this->registerCommands();
        $this->loadViews();
        $this->loadRoutes();
    }

    private function registerPublishes(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes(
            [__DIR__ . '/../config/auth-preset.php' => config_path('auth-preset.php')],
            'auth-preset-config'
        );

        $this->publishes(
            [__DIR__ . '/../routes/web.php' => base_path('routes/auth-preset-web.php')],
            'auth-preset-routes'
        );

        $this->publishes(
            [__DIR__ . '/../routes/api.php' => base_path('routes/auth-preset-api.php')],
            'auth-preset-routes'
        );

        $this->publishes(
            [__DIR__ . '/../resources/views/blade' => resource_path('views/vendor/auth-preset')],
            'auth-preset-views'
        );

    }

    private function registerCommands(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            InstallCommand::class,
        ]);
    }

    private function loadViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'auth-preset');
    }

    private function loadRoutes(): void
    {
        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        $this->app->booted(function (): void {
            if (config(key: 'auth-preset.routes.mode', default: 'package') !== 'package') {
                return;
            }

            $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        });
    }
}
