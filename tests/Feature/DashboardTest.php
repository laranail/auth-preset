<?php

declare(strict_types=1);

use Workbench\App\Models\User;

it(description: 'renders the dashboard for authenticated users', closure: function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertViewIs('auth-preset::blade.dashboard')
        ->assertSee('Dashboard')
        ->assertSee(value: route('user-passkeys.index'), escape: false)
        ->assertSee('Passkeys')
        ->assertSee($user->name);
});

it(description: 'hides the passkeys navigation link when the feature is disabled', closure: function (): void {
    config()->set(key: 'auth-preset.features', value: array_values(array_filter(
        array: config('auth-preset.features'),
        callback: fn (string $feature): bool => $feature !== 'passkeys',
    )));

    $this->actingAs(User::factory()->create())
        ->get('/dashboard')
        ->assertOk()
        ->assertDontSee('>Passkeys<', escape: false);
});

it(description: 'requires authentication to view the dashboard', closure: function (): void {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});
