<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(closure: function (): void {
    $this->socialiteUser = new SocialiteUser();
    $this->socialiteUser->map(attributes: [
        'id'             => '112837291294199545470',
        'name'           => 'Amos Njogu',
        'nickname'       => 'amosnjogu',
        'email'          => 'amos@example.com',
        'avatar'         => 'https://example.com/avatar.jpg',
        'email_verified' => true,
    ]);
    $this->socialiteUser->token = 'mock-token';
    $this->socialiteUser->refreshToken = 'mock-refresh-token';
    $this->socialiteUser->expiresIn = 3600;
});

it(description: 'redirects to dashboard on successful social login', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $this->socialiteUser);

    $response = $this->get(uri: route(name: 'social.callback', parameters: ['provider' => 'google']));

    $response->assertRedirect();
    expect(value: auth()->check())->toBeTrue()
        ->and(value: auth()->user()->email)->toBe(expected: 'amos@example.com');
});

it(description: 'returns existing user when social account already exists', closure: function (): void {
    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $this->socialiteUser);

    $existingUser = User::factory()->create(attributes: ['email' => 'amos@example.com']);
    Social::create([
        'socialable_type' => User::class,
        'socialable_id'   => $existingUser->id,
        'provider'        => 'google',
        'provider_id'     => '112837291294199545470',
        'name'            => 'Amos Njogu',
        'email'           => 'amos@example.com',
        'token'           => 'old-token',
        'refresh_token'   => 'old-refresh-token',
    ]);

    $response = $this->get(uri: route(name: 'social.callback', parameters: ['provider' => 'google']));

    $response->assertRedirect();
    expect(value: auth()->id())->toBe(expected: $existingUser->id);
});

it(description: 'redirects to login on failed social login', closure: function (): void {
    $noEmailUser = new SocialiteUser();
    $noEmailUser->map(attributes: [
        'id'       => '112837291294199545470',
        'name'     => 'No Email',
        'nickname' => 'noemail',
    ]);
    $noEmailUser->token = 'mock-token';
    $noEmailUser->refreshToken = 'mock-refresh';

    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $noEmailUser);

    $response = $this->get(uri: route(name: 'social.callback', parameters: ['provider' => 'google']));

    $response->assertRedirect(uri: route(name: 'login'));
});

it(description: 'does not auto-link by email when provider has not verified it (B1 regression)', closure: function (): void {
    $existingUser = User::factory()->create(attributes: ['email' => 'amos@example.com']);

    $unverifiedUser = new SocialiteUser();
    $raw = [
        'id'             => '112837291294199545470',
        'name'           => 'Attacker',
        'nickname'       => 'attacker',
        'email'          => 'amos@example.com',
        'avatar'         => 'https://example.com/avatar.jpg',
        'email_verified' => false,
    ];
    $unverifiedUser->setRaw(user: $raw);
    $unverifiedUser->map(attributes: $raw);
    $unverifiedUser->token = 'mock-token';
    $unverifiedUser->refreshToken = 'mock-refresh';
    $unverifiedUser->expiresIn = 3600;

    Socialite::fake(driver: SocialProvider::GOOGLE->value, user: $unverifiedUser);

    $response = $this->get(uri: route(name: 'social.callback', parameters: ['provider' => 'google']));

    $response->assertRedirect(uri: route(name: 'login'));
    expect(value: auth()->check())->toBeFalse()
        ->and(value: auth()->id())->not->toBe(expected: $existingUser->id);
});
