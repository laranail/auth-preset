<?php

declare(strict_types=1);

namespace Tests;

use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\Auth\AuthKitServiceProvider;
use Orchestra\Testbench\TestCase as OrchestraTestCase;
use Simtabi\Laranail\AuthPreset\AuthPresetServiceProvider;

abstract class TestCase extends OrchestraTestCase
{
    protected function getPackageProviders($app): array
    {
        return [
            AuthKitServiceProvider::class,
            AuthPresetServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set(key: 'auth-preset.stack', value: 'blade');
        $app['config']->set(key: 'auth-preset.features', value: [
            Features::login(),
            Features::registration(),
            Features::api(),
        ]);
    }
}
