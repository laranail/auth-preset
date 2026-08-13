<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

function bindPasswordUpdater(): void
{
    app()->instance(abstract: UpdatesUserPasswords::class, instance: new class () implements UpdatesUserPasswords {
        public function update($user, array $input): void
        {
            $user->forceFill(['password' => Hash::make($input['password'])])->save();
        }
    });
}

function bindFailingPasswordUpdater(): void
{
    app()->instance(abstract: UpdatesUserPasswords::class, instance: new class () implements UpdatesUserPasswords {
        public function update($user, array $input): void
        {
            throw ValidationException::withMessages([
                'current_password' => 'The provided password does not match your current password.',
            ])->errorBag('updatePassword');
        }
    });
}

it(description: 'renders the password update form for authenticated users', closure: function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('user-password.edit'))
        ->assertOk()
        ->assertViewIs('auth-preset::blade.update-password');
});

it(description: 'updates a password through the web route', closure: function (): void {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    bindPasswordUpdater();

    $this->actingAs($user)
        ->put(uri: route('user-password.update'), data: [
            'current_password'      => 'old-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect();

    expect(Hash::check(value: 'new-password', hashedValue: $user->fresh()->password))->toBeTrue();
});

it(description: 'returns password validation errors in the Fortify error bag', closure: function (): void {
    $user = User::factory()->create();

    bindFailingPasswordUpdater();

    $this->actingAs($user)
        ->from(route('user-password.edit'))
        ->put(uri: route('user-password.update'), data: [
            'current_password'      => 'invalid-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('user-password.edit'))
        ->assertSessionHasErrorsIn(errorBag: 'updatePassword', keys: ['current_password']);
});

it(description: 'updates a password through the Sanctum API route', closure: function (): void {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $token = $user->createToken('test-token');

    bindPasswordUpdater();

    $this->withToken($token->plainTextToken)
        ->putJson(uri: route('api.user-password.update'), data: [
            'current_password'      => 'old-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertOk();

    expect(Hash::check(value: 'new-password', hashedValue: $user->fresh()->password))->toBeTrue();
});
