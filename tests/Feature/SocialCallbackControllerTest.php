<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Laravel\Socialite\Facades\Socialite;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Two\User as SocialiteUser;

beforeEach(function (): void {
    $this->socialiteUser = new SocialiteUser();
    $this->socialiteUser->map([
        'id'             => '112837291294199545470',
        'name'           => 'Amos Njogu',
        'nickname'       => 'amosnjogu',
        'email'          => 'amos@simtabi.com',
        'avatar'         => 'https://example.com/avatar.jpg',
        'email_verified' => true,
    ]);
    $this->socialiteUser->token = 'mock-token';
    $this->socialiteUser->refreshToken = 'mock-refresh-token';
    $this->socialiteUser->expiresIn = 3600;
});

it('redirects to dashboard on successful social login', function (): void {
    Socialite::fake(SocialProvider::GOOGLE->value, $this->socialiteUser);

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response->assertRedirect();
    expect(auth()->check())->toBeTrue()
        ->and(auth()->user()->email)->toBe('amos@simtabi.com');
});

it('returns existing user when social account already exists', function (): void {
    Socialite::fake(SocialProvider::GOOGLE->value, $this->socialiteUser);

    $existingUser = User::factory()->create(['email' => 'amos@simtabi.com']);
    Social::create([
        'socialable_type' => User::class,
        'socialable_id'   => $existingUser->id,
        'provider'        => 'google',
        'provider_id'     => '112837291294199545470',
        'name'            => 'Amos Njogu',
        'email'           => 'amos@simtabi.com',
        'token'           => 'old-token',
        'refresh_token'   => 'old-refresh-token',
    ]);

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response->assertRedirect();
    expect(auth()->id())->toBe($existingUser->id);
});

it('redirects to login on failed social login', function (): void {
    $noEmailUser = new SocialiteUser();
    $noEmailUser->map([
        'id'       => '112837291294199545470',
        'name'     => 'No Email',
        'nickname' => 'noemail',
    ]);
    $noEmailUser->token = 'mock-token';
    $noEmailUser->refreshToken = 'mock-refresh';

    Socialite::fake(SocialProvider::GOOGLE->value, $noEmailUser);

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
});

it('does not auto-link by email when provider has not verified it (B1 regression)', function (): void {
    $existingUser = User::factory()->create(['email' => 'amos@simtabi.com']);

    $unverifiedUser = new SocialiteUser();
    $raw = [
        'id'             => '112837291294199545470',
        'name'           => 'Attacker',
        'nickname'       => 'attacker',
        'email'          => 'amos@simtabi.com',
        'avatar'         => 'https://example.com/avatar.jpg',
        'email_verified' => false,
    ];
    $unverifiedUser->setRaw($raw);
    $unverifiedUser->map($raw);
    $unverifiedUser->token = 'mock-token';
    $unverifiedUser->refreshToken = 'mock-refresh';
    $unverifiedUser->expiresIn = 3600;

    Socialite::fake(SocialProvider::GOOGLE->value, $unverifiedUser);

    $response = $this->get(route('social.callback', ['provider' => 'google']));

    $response->assertRedirect(route('login'));
    expect(auth()->check())->toBeFalse()
        ->and(auth()->id())->not->toBe($existingUser->id);
});
