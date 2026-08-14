<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Support\Facades\Http;
use Simtabi\Laranail\AuthPreset\Features;

it('renders Turnstile on protected guest forms but not login when enabled', function (): void {
    config()->set('auth-preset.features', array_merge(
        config(key: 'auth-preset.features'),
        [Features::turnstile()],
    ));
    config()->set('auth-kit.turnstile.site_key', 'site-key');

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('cf-turnstile', escape: false)
        ->assertDontSee('challenges.cloudflare.com/turnstile/v0/api.js', escape: false);

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('cf-turnstile', escape: false);

    $this->get(route('password.request'))
        ->assertOk()
        ->assertSee('cf-turnstile', escape: false);

    $this->get(route('password.reset', ['token' => 'test-token']))
        ->assertOk()
        ->assertSee('cf-turnstile', escape: false);
});

it('does not render Turnstile when the feature is disabled', function (): void {
    config()->set('auth-preset.features', array_values(array_filter(
        array: config('auth-preset.features'),
        callback: fn (string $feature): bool => $feature !== Features::turnstile(),
    )));
    config()->set('auth-kit.turnstile.enabled', false);

    $this->get(route('login'))
        ->assertOk()
        ->assertDontSee('cf-turnstile', escape: false)
        ->assertDontSee('challenges.cloudflare.com/turnstile/v0/api.js', escape: false);

    $this->get(route('register'))
        ->assertOk()
        ->assertDontSee('cf-turnstile', escape: false);

    $this->get(route('password.request'))
        ->assertOk()
        ->assertDontSee('cf-turnstile', escape: false);

    $this->get(route('password.reset', ['token' => 'test-token']))
        ->assertOk()
        ->assertDontSee('cf-turnstile', escape: false);
});

it('does not challenge API login when the feature is enabled', function (): void {
    config()->set('auth-preset.features', array_merge(
        config(key: 'auth-preset.features'),
        [Features::turnstile()],
    ));
    config()->set('auth-kit.turnstile.enabled', true);
    Http::fake();

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->postJson(route('api.login'), [
        'email'    => 'ada@example.com',
        'password' => 'password',
    ])->assertOk();

    Http::assertNothingSent();
});

it('does not challenge web login when the feature is enabled', function (): void {
    config()->set('auth-preset.features', array_merge(
        config(key: 'auth-preset.features'),
        [Features::turnstile()],
    ));
    config()->set('auth-kit.turnstile.enabled', true);
    Http::fake();

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('password'),
    ]);

    $this->post(route('login.store'), [
        'email'    => 'ada@example.com',
        'password' => 'password',
    ])->assertRedirect();

    Http::assertNothingSent();
});

it('rejects missing Turnstile tokens on guest submissions', function (): void {
    config()->set('auth-preset.features', array_merge(
        config(key: 'auth-preset.features'),
        [Features::turnstile()],
    ));
    config()->set('auth-kit.turnstile.enabled', true);
    Http::fake();

    $this->from(route('register'))
        ->post(route('register.store'), [
            'name'                  => 'Ada Lovelace',
            'email'                 => 'ada@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('register'))
        ->assertSessionHasErrors('cf-turnstile-response');

    $this->from(route('password.request'))
        ->post(route('password.email'), ['email' => 'ada@example.com'])
        ->assertRedirect(route('password.request'))
        ->assertSessionHasErrors('cf-turnstile-response');

    $this->from(route('password.reset', ['token' => 'test-token']))
        ->post(route('password.update'), [
            'token'                 => 'test-token',
            'email'                 => 'ada@example.com',
            'password'              => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('password.reset', ['token' => 'test-token']))
        ->assertSessionHasErrors('cf-turnstile-response');

    Http::assertNothingSent();
});
