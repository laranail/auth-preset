<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Workbench\App\Models\User;

it('returns a token on API login', function (): void {
    $password = Str::random(16);

    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt($password),
    ]);

    $response = $this->postJson(route('api.login'), [
        'email'    => 'ada@example.com',
        'password' => $password,
    ]);

    $response->assertOk()
        ->assertJsonStructure(['token', 'user']);
});

it('returns 422 on API login with wrong credentials', function (): void {
    User::factory()->create([
        'email'    => 'ada@example.com',
        'password' => bcrypt('correct-password'),
    ]);

    $response = $this->postJson(route('api.login'), [
        'email'    => 'ada@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(422)
        ->assertJsonPath('status', 'failed');
});

it('returns a token on API registration', function (): void {
    $password = Str::password(12);

    $response = $this->postJson(route('api.register'), [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => $password,
        'password_confirmation' => $password,
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
