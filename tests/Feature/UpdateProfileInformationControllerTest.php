<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

function bindProfileInformationUpdater(): void
{
    app()->instance(UpdatesUserProfileInformation::class, new class () implements UpdatesUserProfileInformation {
        public function update($user, array $input): void
        {
            $user->forceFill([
                'name'  => $input['name'],
                'email' => $input['email'],
            ])->save();
        }
    });
}

function bindFailingProfileInformationUpdater(): void
{
    app()->instance(UpdatesUserProfileInformation::class, new class () implements UpdatesUserProfileInformation {
        public function update($user, array $input): void
        {
            throw ValidationException::withMessages([
                'email' => 'The email address is already in use.',
            ])->errorBag('updateProfileInformation');
        }
    });
}

it('renders the profile information form for authenticated users', function (): void {
    $user = User::factory()->create([
        'name'  => 'Ada Lovelace',
        'email' => 'ada@example.com',
    ]);

    $this->actingAs($user)
        ->get(route('user-profile-information.edit'))
        ->assertOk()
        ->assertViewIs('auth-preset::blade.update-profile-information')
        ->assertSee('Ada Lovelace')
        ->assertSee('ada@example.com');
});

it('updates profile information through the web route', function (): void {
    $user = User::factory()->create();

    bindProfileInformationUpdater();

    $this->actingAs($user)
        ->put(route('user-profile-information.update'), [
            'name'  => 'Grace Hopper',
            'email' => 'grace@example.com',
        ])
        ->assertRedirect();

    expect($user->fresh()->only(['name', 'email']))->toBe([
        'name'  => 'Grace Hopper',
        'email' => 'grace@example.com',
    ]);
});

it('returns profile validation errors in the Fortify error bag', function (): void {
    $user = User::factory()->create();

    bindFailingProfileInformationUpdater();

    $this->actingAs($user)
        ->from(route('user-profile-information.edit'))
        ->put(route('user-profile-information.update'), [
            'name'  => 'Grace Hopper',
            'email' => 'taken@example.com',
        ])
        ->assertRedirect(route('user-profile-information.edit'))
        ->assertSessionHasErrorsIn('updateProfileInformation', ['email']);
});

it('updates profile information through the Sanctum API route', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    bindProfileInformationUpdater();

    $this->withToken($token->plainTextToken)
        ->putJson(route('api.user-profile-information.update'), [
            'name'  => 'Grace Hopper',
            'email' => 'grace@example.com',
        ])
        ->assertOk();

    expect($user->fresh()->only(['name', 'email']))->toBe([
        'name'  => 'Grace Hopper',
        'email' => 'grace@example.com',
    ]);
});
