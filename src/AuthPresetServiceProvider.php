<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Illuminate\Support\ServiceProvider;
use Simtabi\Laranail\AuthPreset\Commands\InstallCommand;

class AuthPresetServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/auth-preset.php', 'auth-preset');

        config()->set(
            key: 'auth-kit.turnstile.enabled',
            value: Features::enabled(Features::turnstile()),
        );
    }

    public function boot(): void
    {
        $this->registerPublishes();
        $this->registerCommands();
        $this->loadViews();
        $this->registerFortifyViews();
        $this->loadRoutes();
        $this->app->booted(function (): void {
            $this->registerTurnstileMiddleware();
        });
    }

    private function registerFortifyViews(): void
    {
        if (! Features::enabled(Features::login())) {
            return;
        }

        Fortify::loginView(fn () => view(Support\AuthPreset::view('login')));

        if (Features::enabled(Features::registration())) {
            Fortify::registerView(fn () => view(Support\AuthPreset::view('register')));
        }

        if (Features::enabled(Features::passwordReset())) {
            Fortify::requestPasswordResetLinkView(fn () => view(Support\AuthPreset::view('forgot-password')));
            Fortify::resetPasswordView(fn (Request $request) => view(Support\AuthPreset::view('reset-password'), [
                'request' => $request,
            ]));
        }

        if (Features::enabled(Features::emailVerification())) {
            Fortify::verifyEmailView(fn () => view(Support\AuthPreset::view('verify-email')));
        }
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
        if (config(key: 'auth-preset.routes.mode', default: 'package') !== 'package') {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
    }

    private function registerTurnstileMiddleware(): void
    {
        foreach (['register.store', 'password.email', 'password.update'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);

            if ($route !== null && in_array('web', $route->middleware(), true)) {
                $route->middleware(\Simtabi\Laranail\Auth\Http\Middleware\ValidateTurnstile::class);
            }
        }
    }
}
