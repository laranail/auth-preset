<?php

declare(strict_types=1);

use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;
use Simtabi\Laranail\AuthPreset\Enums\AuthenticationFeature;

it(description: 'returns default blade stack', closure: function (): void {
    expect(AuthPreset::stack())->toBe(FrontendStack::Blade);
});

it(description: 'reads features from the Fortify-style feature list', closure: function (): void {
    expect(Features::enabled(Features::login()))->toBeTrue()
        ->and(Features::enabled(Features::registration()))->toBeTrue()
        ->and(Features::enabled(Features::api()))->toBeTrue();
});

it(description: 'can disable a feature by omitting it from the list', closure: function (): void {
    config()->set('auth-preset.features', [Features::login()]);

    expect(Features::enabled(Features::login()))->toBeTrue()
        ->and(Features::enabled(Features::registration()))->toBeFalse()
        ->and(Features::enabled(Features::api()))->toBeFalse();
});

it('exposes Enumerator metadata for authentication features', function (): void {
    expect(AuthenticationFeature::values())->toContain('login', 'social', 'turnstile')
        ->and(AuthenticationFeature::LOGIN->label())->toBe('Login')
        ->and(AuthenticationFeature::SOCIAL->description())
        ->toBe('Adds OAuth callback routes for the providers selected next.')
        ->and(AuthenticationFeature::options())->toMatchArray([
            'login'        => 'Login',
            'registration' => 'Registration',
            'social'       => 'Social login',
        ]);
});

it('ignores invalid configured social providers before checking credentials', function (): void {
    config()->set('auth-preset.social.providers', ['google', 123, 'github']);
    config()->set('auth-kit.social.google.client_id', 'client-id');

    expect(AuthPreset::enabledSocialProviders())->toBe(['google']);
});

it(description: 'returns correct prefix values', closure: function (): void {
    expect(AuthPreset::webPrefix())->toBe('auth')
        ->and(AuthPreset::apiPrefix())->toBe('api/auth');
});

it(description: 'returns correct redirects', closure: function (): void {
    expect(AuthPreset::afterLoginRedirect())->toBe('/dashboard');
    expect(AuthPreset::afterRegistrationRedirect())->toBe('/dashboard');
});
