<?php

declare(strict_types=1);

use Workbench\App\Models\User;

it('returns a token on API login', function (): void {
    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $response = $this->postJson(route('api.login'), [
        'email'    => 'ada@example.com',
        'password' => 'secret',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('returns 422 on API login with wrong credentials', function (): void {
    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('secret'),
    ]);

    $response = $this->postJson(route('api.login'), [
        'email'    => 'ada@example.com',
        'password' => 'wrong',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('status', 'failed');
});

it('returns a token on API registration', function (): void {
    $response = $this->postJson(route('api.register'), [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => 'SecureP@ss1',
        'password_confirmation' => 'SecureP@ss1',
    ]);

    $response->assertStatus(201)
        ->assertJsonStructure(['token', 'user']);
});

it('revokes the token on API logout', function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    $response = $this->withToken($token->plainTextToken)
        ->postJson(route('api.logout'));

    $response->assertOk()
        ->assertJsonPath('status', 'logged_out');

    expect($user->fresh()->tokens)->toHaveCount(0);
});
