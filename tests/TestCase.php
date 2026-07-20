<?php

declare(strict_types=1);

namespace Tests;

use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Simtabi\Laranail\AuthPreset\AuthPresetServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AuthPresetServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set(key: 'auth-preset.stack', value: 'blade');
        $app['config']->set(key: 'auth-preset.features.email_login', value: true);
        $app['config']->set(key: 'auth-preset.features.username_login', value: true);
        $app['config']->set(key: 'auth-preset.features.api_routes', value: true);
        $app['config']->set(key: 'auth-preset.features.web_routes', value: true);
    }
}
