<?php

declare(strict_types=1);

namespace Tests;

use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\Auth\AuthKitServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Simtabi\Laranail\AuthPreset\AuthPresetServiceProvider;
use Simtabi\Laranail\Captcha\Providers\CaptchaServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            \Laravel\Socialite\SocialiteServiceProvider::class,
            \Laravel\Fortify\FortifyServiceProvider::class,
            \Laravel\Sanctum\SanctumServiceProvider::class,
            AuthKitServiceProvider::class,
            CaptchaServiceProvider::class,
            AuthPresetServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver'                  => 'sqlite',
            'database'                => ':memory:',
            'prefix'                  => '',
            'foreign_key_constraints' => true,
        ]);

        $app['config']->set('auth.providers.users.model', \Workbench\App\Models\User::class);
        $app['config']->set('auth-kit.user_model', \Workbench\App\Models\User::class);

        $app['config']->set('auth-preset.stack', 'blade');
        $app['config']->set('auth-preset.features', [
            Features::login(),
            Features::registration(),
            Features::logout(),
            Features::social(),
            Features::api(),
            Features::passwordReset(),
            Features::updateProfileInformation(),
            Features::updatePasswords(),
            Features::emailVerification(),
            Features::passkeys(),
        ]);
    }

    protected function defineDatabaseMigrations(): void
    {
        $authKitPasskeyMigrations = dirname(__DIR__) . '/vendor/laranail/auth-kit/database/migrations/passkeys';

        if (! is_dir($authKitPasskeyMigrations)) {
            $authKitPasskeyMigrations = dirname(__DIR__, 2) . '/laranail-auth-kit/database/migrations/passkeys';
        }

        $this->loadMigrationsFrom(dirname(__DIR__) . '/vendor/orchestra/testbench-core/laravel/migrations');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/vendor/laravel/fortify/database/migrations');
        $this->loadMigrationsFrom($authKitPasskeyMigrations);
        $this->loadMigrationsFrom(dirname(__DIR__) . '/vendor/laravel/sanctum/database/migrations');
        $this->loadMigrationsFrom(dirname(__DIR__) . '/vendor/laranail/auth-kit/database/migrations/social');
    }
}
