<?php

declare(strict_types=1);

use Illuminate\Support\Str;
use Workbench\App\Models\User;

it(description: 'returns a token on API login', closure: function (): void {
    $password = Str::random(length: 16);

    User::factory()->create(attributes: [
        'email'    => 'ada@example.com',
        'password' => bcrypt(value: $password),
    ]);

    $response = $this->postJson(uri: route(name: 'api.login'), data: [
        'email'    => 'ada@example.com',
        'password' => $password,
    ]);

    $response->assertOk()
        ->assertJsonPath(path: 'status', expect: 'success')
        ->assertJsonStructure(structure: ['status', 'data' => ['token', 'user']]);
});

it(description: 'returns 422 on API login with wrong credentials', closure: function (): void {
    User::factory()->create(attributes: [
        'email'    => 'ada@example.com',
        'password' => bcrypt(value: 'correct-password'),
    ]);

    $response = $this->postJson(uri: route(name: 'api.login'), data: [
        'email'    => 'ada@example.com',
        'password' => 'wrong-password',
    ]);

    $response->assertStatus(status: 422)
        ->assertJsonPath(path: 'status', expect: 'failed')
        ->assertJsonPath(path: 'data.message', expect: 'Invalid credentials.');
});

it(description: 'returns a token on API registration', closure: function (): void {
    $password = Str::password(length: 12);

    $response = $this->postJson(uri: route(name: 'api.register'), data: [
        'name'                  => 'Ada Lovelace',
        'email'                 => 'ada@example.com',
        'password'              => $password,
        'password_confirmation' => $password,
    ]);

    $response->assertStatus(status: 201)
        ->assertJsonPath(path: 'status', expect: 'success')
        ->assertJsonStructure(structure: ['status', 'data' => ['token', 'user']]);
});

it(description: 'revokes the token on API logout', closure: function (): void {
    $user = User::factory()->create();
    $token = $user->createToken('test-token');

    $response = $this->withToken(token: $token->plainTextToken)
        ->postJson(uri: route(name: 'api.logout'));

    $response->assertOk()
        ->assertJsonPath(path: 'status', expect: 'success')
        ->assertJsonPath(path: 'data.message', expect: 'Logged out successfully.');

    expect(value: $user->fresh()->tokens)->toHaveCount(count: 0);
});
