<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Simtabi\Laranail\Auth\Models\Social;
use Simtabi\Laranail\Auth\Enums\SocialProvider;
use Laravel\Socialite\Two\User as SocialiteUser;
use Simtabi\Laranail\AuthPreset\Http\Controllers\Auth\SocialCallbackController;

beforeEach(function (): void {
    $this->socialiteUser = new SocialiteUser();
    $this->socialiteUser->map([
        'id'       => '112837291294199545470',
        'name'     => 'Amos Njogu',
        'nickname' => 'amosnjogu',
        'email'    => 'amos@simtabi.com',
        'avatar'   => 'https://example.com/avatar.jpg',
    ]);
    $this->socialiteUser->token = 'mock-token';
    $this->socialiteUser->refreshToken = 'mock-refresh-token';
    $this->socialiteUser->expiresIn = 3600;
});

it('resolves user model from config when no social account exists', function (): void {
    $controller = app(SocialCallbackController::class);
    $closure = (new ReflectionMethod($controller, 'resolve'))->invoke($controller, SocialProvider::GOOGLE);
    $user = $closure($this->socialiteUser);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->email)->toBe('amos@simtabi.com')
        ->and($user->name)->toBe('Amos Njogu');
});

it('returns existing user when social account already exists', function (): void {
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

    $controller = app(SocialCallbackController::class);
    $closure = (new ReflectionMethod($controller, 'resolve'))->invoke($controller, SocialProvider::GOOGLE);
    $user = $closure($this->socialiteUser);

    expect($user->id)->toBe($existingUser->id)
        ->and(Social::count())->toBe(1);
});

it('links social account to existing user with matching email', function (): void {
    $existingUser = User::factory()->create(['email' => 'amos@simtabi.com']);

    $controller = app(SocialCallbackController::class);
    $closure = (new ReflectionMethod($controller, 'resolve'))->invoke($controller, SocialProvider::GOOGLE);
    $user = $closure($this->socialiteUser);

    expect($user->id)->toBe($existingUser->id)
        ->and(Social::where('provider', 'google')->count())->toBe(1)
        ->and(Social::first()->socialable_id)->toBe($existingUser->id)
        ->and(Social::first()->provider->value)->toBe('google');
});

it('creates new user and social account when nothing exists', function (): void {
    $controller = app(SocialCallbackController::class);
    $closure = (new ReflectionMethod($controller, 'resolve'))->invoke($controller, SocialProvider::GOOGLE);
    $user = $closure($this->socialiteUser);

    expect($user)->toBeInstanceOf(User::class)
        ->and($user->email)->toBe('amos@simtabi.com')
        ->and(Social::count())->toBe(1)
        ->and(Social::first()->provider->value)->toBe('google')
        ->and(Social::first()->provider_id)->toBe('112837291294199545470');
});

it('uses provider value correctly without variable shadowing', function (): void {
    $controller = app(SocialCallbackController::class);
    $closure = (new ReflectionMethod($controller, 'resolve'))->invoke($controller, SocialProvider::FACEBOOK);
    $user = $closure($this->socialiteUser);

    expect(Social::first()->provider->value)->toBe('facebook')
        ->and(Social::first()->provider_id)->toBe('112837291294199545470');
});
