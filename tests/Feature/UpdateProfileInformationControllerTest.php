<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserProfileInformation;

function bindProfileInformationUpdater(): void
{
    app()->instance(abstract: UpdatesUserProfileInformation::class, instance: new class () implements UpdatesUserProfileInformation {
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
    app()->instance(abstract: UpdatesUserProfileInformation::class, instance: new class () implements UpdatesUserProfileInformation {
        public function update($user, array $input): void
        {
            throw ValidationException::withMessages([
                'email' => 'The email address is already in use.',
            ])->errorBag('updateProfileInformation');
        }
    });
}

it(description: 'renders the profile information form for authenticated users', closure: function (): void {
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

it(description: 'updates profile information through the web route', closure: function (): void {
    $user = User::factory()->create();

    bindProfileInformationUpdater();

    $this->actingAs($user)
        ->put(uri: route('user-profile-information.update'), data: [
            'name'  => 'Grace Hopper',
            'email' => 'grace@example.com',
        ])
        ->assertRedirect();

    expect($user->fresh()->only(['name', 'email']))->toBe([
        'name'  => 'Grace Hopper',
        'email' => 'grace@example.com',
    ]);
});

it(description: 'returns profile validation errors in the Fortify error bag', closure: function (): void {
    $user = User::factory()->create();

    bindFailingProfileInformationUpdater();

    $this->actingAs($user)
        ->from(route('user-profile-information.edit'))
        ->put(uri: route('user-profile-information.update'), data: [
            'name'  => 'Grace Hopper',
            'email' => 'taken@example.com',
        ])
        ->assertRedirect(route('user-profile-information.edit'))
        ->assertSessionHasErrorsIn(errorBag: 'updateProfileInformation', keys: ['email']);
});

it(description: 'updates profile information through the Sanctum API route', closure: function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    bindProfileInformationUpdater();

    $this->withToken($token->plainTextToken)
        ->putJson(uri: route('api.user-profile-information.update'), data: [
            'name'  => 'Grace Hopper',
            'email' => 'grace@example.com',
        ])
        ->assertOk();

    expect($user->fresh()->only(['name', 'email']))->toBe([
        'name'  => 'Grace Hopper',
        'email' => 'grace@example.com',
    ]);
});
