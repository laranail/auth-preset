<?php

declare(strict_types=1);

use Simtabi\Laranail\AuthPreset\Enums\FrontendStack;

it(description: 'has correct frontend stack values', closure: function (): void {
    expect(FrontendStack::Blade->value)->toBe('blade')
        ->and(FrontendStack::Livewire->value)->toBe('livewire')
        ->and(FrontendStack::InertiaVue->value)->toBe('inertia-vue')
        ->and(FrontendStack::InertiaReact->value)->toBe('inertia-react');
});

it(description: 'can resolve stack from string', closure: function (): void {
    expect(FrontendStack::from('blade'))->toBe(FrontendStack::Blade)
        ->and(FrontendStack::from('livewire'))->toBe(FrontendStack::Livewire)
        ->and(FrontendStack::from('inertia-vue'))->toBe(FrontendStack::InertiaVue)
        ->and(FrontendStack::from('inertia-react'))->toBe(FrontendStack::InertiaReact);
});
