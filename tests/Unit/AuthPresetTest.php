<?php

declare(strict_types=1);

use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;

it(description: 'returns default blade stack', closure: function (): void {
    expect(AuthPreset::stack())->toBe(FrontendStack::Blade);
});

it(description: 'reads feature toggles from config', closure: function (): void {
    expect(AuthPreset::enabled('email_login'))->toBeTrue()
        ->and(AuthPreset::enabled('username_login'))->toBeTrue()
        ->and(AuthPreset::enabled('api_routes'))->toBeTrue()
        ->and(AuthPreset::enabled('web_routes'))->toBeTrue();
});

it(description: 'returns correct prefix values', closure: function (): void {
    expect(AuthPreset::webPrefix())->toBe('auth')
        ->and(AuthPreset::apiPrefix())->toBe('api/auth');
});

it(description: 'returns correct redirects', closure: function (): void {
    expect(AuthPreset::afterLoginRedirect())->toBe('/dashboard');
});

it(description: 'isInertia returns true for both inertia stacks', closure: function (): void {
    config()->set(key: 'auth-preset.stack', value: 'inertia-vue');
    expect(AuthPreset::isInertia())->toBeTrue();

    config()->set(key: 'auth-preset.stack', value: 'inertia-react');
    expect(AuthPreset::isInertia())->toBeTrue();

    config()->set(key: 'auth-preset.stack', value: 'blade');
    expect(AuthPreset::isInertia())->toBeFalse();
});
