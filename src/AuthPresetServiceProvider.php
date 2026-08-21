<?php

declare(strict_types=1);

namespace Simtabi\Laranail\AuthPreset;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use Simtabi\Laranail\Package\Tools\Package;
use Simtabi\Laranail\AuthPreset\Commands\InstallCommand;
use Simtabi\Laranail\AuthPreset\Http\Middleware\ValidateCaptcha;
use Simtabi\Laranail\Package\Tools\Providers\PackageServiceProvider;

class AuthPresetServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('laranail/auth-preset')
            ->publish(
                ['config/auth-preset.php' => config_path('auth-preset.php')],
                'auth-preset-config',
            )
            ->publish(
                ['routes/web.php' => base_path('routes/auth-preset-web.php')],
                'auth-preset-routes',
            )
            ->publish(
                ['routes/api.php' => base_path('routes/auth-preset-api.php')],
                'auth-preset-routes',
            )
            ->publish(
                ['resources/views/blade' => resource_path('views/vendor/auth-preset')],
                'auth-preset-views',
            );
    }

    public function packageRegistered(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/auth-preset.php', 'auth-preset');

        config()->set('auth-kit.turnstile.enabled', false);
        config()->set('laranail.captcha.provider', config('auth-preset.bot_protection.provider', 'turnstile'));
        config()->set('laranail.captcha.credentials.source', 'config');
        config()->set('laranail.captcha.credentials.database.enabled', false);
    }

    public function packageBooted(): void
    {
        $this->registerCommands();
        $this->loadViews();
        $this->registerFortifyViews();
        $this->loadRoutes();
        $this->app->booted(function (): void {
            $this->registerCaptchaMiddleware();
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

    private function registerCaptchaMiddleware(): void
    {
        foreach (['login.store', 'register.store', 'password.email', 'password.update'] as $name) {
            $route = app('router')->getRoutes()->getByName($name);

            if ($route !== null && in_array('web', $route->middleware(), true)) {
                $route->middleware(ValidateCaptcha::class);
            }
        }
    }
}
