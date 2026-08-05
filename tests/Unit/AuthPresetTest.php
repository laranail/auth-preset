<?php

declare(strict_types=1);

use Simtabi\Laranail\AuthPreset\Features;
use Simtabi\Laranail\AuthPreset\Support\AuthPreset;
use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;

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

it(description: 'returns correct prefix values', closure: function (): void {
    expect(AuthPreset::webPrefix())->toBe('auth')
        ->and(AuthPreset::apiPrefix())->toBe('api/auth');
});

it(description: 'returns correct redirects', closure: function (): void {
    expect(AuthPreset::afterLoginRedirect())->toBe('/dashboard');
    expect(AuthPreset::afterRegistrationRedirect())->toBe('/dashboard');
});
