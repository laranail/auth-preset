<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Contracts\PasskeyUser;

it(description: 'requires the workbench user to implement the passkey contract', closure: function (): void {
    expect(User::factory()->create())->toBeInstanceOf(PasskeyUser::class);
});

it(description: 'renders an empty passkey management page for authenticated users', closure: function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('user-passkeys.index'))
        ->assertOk()
        ->assertViewIs('auth-preset::blade.passkeys')
        ->assertSee(value: 'data-passkey-management', escape: false)
        ->assertSee('No passkeys registered yet.')
        ->assertSee(value: route('passkey.registration-options'), escape: false)
        ->assertSee(value: route('passkey.store'), escape: false)
        ->assertSee(value: 'data-passkey-register-error', escape: false);
});

it(description: 'renders multiple registered passkeys with deletion hooks', closure: function (): void {
    $user = User::factory()->create();

    $first = $user->passkeys()->create([
        'name'          => 'MacBook Pro',
        'credential_id' => 'credential-one',
        'credential'    => ['public-key' => 'one'],
    ]);
    $second = $user->passkeys()->create([
        'name'          => 'iPhone',
        'credential_id' => 'credential-two',
        'credential'    => ['public-key' => 'two'],
    ]);

    $this->actingAs($user)
        ->get(route('user-passkeys.index'))
        ->assertOk()
        ->assertSee('MacBook Pro')
        ->assertSee('iPhone')
        ->assertSee(value: route(name: 'passkey.destroy', parameters: ['passkey' => $first]), escape: false)
        ->assertSee(value: route(name: 'passkey.destroy', parameters: ['passkey' => $second]), escape: false)
        ->assertSee(value: 'data-passkey-delete', escape: false)
        ->assertSee(value: 'data-passkey-delete-error', escape: false);
});

it(description: 'protects the passkey management page with the configured guard', closure: function (): void {
    $route = Route::getRoutes()->getByName('user-passkeys.index');

    expect($route->middleware())->toContain('web')
        ->and($route->middleware())->toContain('auth:web');
});

it(description: 'adds passkey login hooks to the login view when enabled', closure: function (): void {
    $this->get(route('login'))
        ->assertOk()
        ->assertSee(value: 'data-passkey-login', escape: false)
        ->assertSee(value: route('passkey.login-options'), escape: false)
        ->assertSee(value: route('passkey.login'), escape: false)
        ->assertSee(value: 'data-passkey-error', escape: false)
        ->assertSee('Sign in with a passkey');
});

it(description: 'hides passkey login hooks when the preset feature is disabled', closure: function (): void {
    config()->set(key: 'auth-preset.features', value: array_values(array_filter(
        array: config('auth-preset.features'),
        callback: fn (string $feature): bool => $feature !== 'passkeys',
    )));

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee(value: 'data-passkey-login', escape: false)
        ->assertDontSee('Sign in with a passkey');
});
