<?php

declare(strict_types=1);

it('renders the configured captcha provider on protected guest forms', function (): void {
    config()->set('auth-preset.features', array_merge(
        config(key: 'auth-preset.features'),
        ['bot-protection'],
    ));
    config()->set('laranail.captcha.provider', 'turnstile');
    config()->set('laranail.captcha.credentials.source', 'config');

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('cf-turnstile', escape: false);

    $this->get(route('login'))
        ->assertOk()
        ->assertSee('cf-turnstile', escape: false);
});

it('uses Turnstile and config credentials by default', function (): void {
    expect(config('auth-preset.bot_protection.provider'))->toBe('turnstile')
        ->and(config('laranail.captcha.provider'))->toBe('turnstile')
        ->and(config('laranail.captcha.credentials.source'))->toBe('config')
        ->and(config('laranail.captcha.credentials.database.enabled'))->toBeFalse();
});

it('renders another configured captcha provider without changing the form', function (): void {
    config()->set('auth-preset.features', array_merge(
        config(key: 'auth-preset.features'),
        ['bot-protection'],
    ));
    config()->set('laranail.captcha.provider', 'math');

    $this->get(route('register'))
        ->assertOk()
        ->assertSee('laranail-captcha-question', escape: false)
        ->assertDontSee('cf-turnstile', escape: false);
});
