<?php

declare(strict_types=1);

use Workbench\App\Models\User;

it(description: 'renders the dashboard for authenticated users', closure: function (): void {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get('/dashboard')
        ->assertOk()
        ->assertViewIs('auth-preset::blade.dashboard')
        ->assertSee('Dashboard')
        ->assertSee($user->name);
});

it(description: 'requires authentication to view the dashboard', closure: function (): void {
    $this->get('/dashboard')
        ->assertRedirect(route('login'));
});
