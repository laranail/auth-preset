<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset;

use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
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
        $this->registerLivewireComponents();
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

        $this->publishes(
            [__DIR__ . '/../resources/views/livewire' => resource_path('views/livewire')],
            'auth-preset-livewire'
        );

        $this->publishes(
            [__DIR__ . '/../src/Livewire' => app_path('Livewire')],
            'auth-preset-livewire'
        );

        $this->publishes(
            [__DIR__ . '/../resources/views/inertia' => resource_path('js/Pages/auth')],
            'auth-preset-inertia'
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
            $features = config('auth-preset.features', []);

            if (($features['web_routes'] ?? false) && file_exists($path = base_path('routes/auth-preset-web.php'))) {
                require $path;
            } elseif ($features['web_routes'] ?? false) {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            }

            if (($features['api_routes'] ?? false) && file_exists($path = base_path('routes/auth-preset-api.php'))) {
                require $path;
            } elseif ($features['api_routes'] ?? false) {
                $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
            }
        });
    }

    private function registerLivewireComponents(): void
    {
        if (AuthPreset::stack() !== \Simtabi\Laranail\AuthPreset\Enums\FrontendStack::Livewire) {
            return;
        }

        if (! class_exists(\Livewire\Livewire::class)) {
            return;
        }

        \Livewire\Livewire::component('auth-preset.login', \Simtabi\Laranail\AuthPreset\Livewire\Login::class);
        \Livewire\Livewire::component('auth-preset.username-login', \Simtabi\Laranail\AuthPreset\Livewire\UsernameLogin::class);
        \Livewire\Livewire::component('auth-preset.check-email-exists', \Simtabi\Laranail\AuthPreset\Livewire\CheckEmailExists::class);
        \Livewire\Livewire::component('auth-preset.check-username-exists', \Simtabi\Laranail\AuthPreset\Livewire\CheckUsernameExists::class);
    }
}
