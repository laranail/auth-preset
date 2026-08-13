<?php

declare(strict_types=1);

use Workbench\App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Fortify\Contracts\UpdatesUserPasswords;

function bindPasswordUpdater(): void
{
    app()->instance(UpdatesUserPasswords::class, new class () implements UpdatesUserPasswords {
        public function update($user, array $input): void
        {
            $user->forceFill(['password' => Hash::make($input['password'])])->save();
        }
    });
}

function bindFailingPasswordUpdater(): void
{
    app()->instance(UpdatesUserPasswords::class, new class () implements UpdatesUserPasswords {
        public function update($user, array $input): void
        {
            throw ValidationException::withMessages([
                'current_password' => 'The provided password does not match your current password.',
            ])->errorBag('updatePassword');
        }
    });
}

it('renders the password update form for authenticated users', function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('user-password.edit'))
        ->assertOk()
        ->assertViewIs('auth-preset::blade.update-password');
});

it('updates a password through the web route', function (): void {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);

    bindPasswordUpdater();

    $this->actingAs($user)
        ->put(route('user-password.update'), [
            'current_password'      => 'old-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});

it('returns password validation errors in the Fortify error bag', function (): void {
    $user = User::factory()->create();

    bindFailingPasswordUpdater();

    $this->actingAs($user)
        ->from(route('user-password.edit'))
        ->put(route('user-password.update'), [
            'current_password'      => 'invalid-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertRedirect(route('user-password.edit'))
        ->assertSessionHasErrorsIn('updatePassword', ['current_password']);
});

it('updates a password through the Sanctum API route', function (): void {
    $user = User::factory()->create(['password' => Hash::make('old-password')]);
    $token = $user->createToken('test-token');

    bindPasswordUpdater();

    $this->withToken($token->plainTextToken)
        ->putJson(route('api.user-password.update'), [
            'current_password'      => 'old-password',
            'password'              => 'new-password',
            'password_confirmation' => 'new-password',
        ])
        ->assertOk();

    expect(Hash::check('new-password', $user->fresh()->password))->toBeTrue();
});
