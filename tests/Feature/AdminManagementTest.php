<?php

use App\Enums\UserRole;
use App\Models\User;

test('admin can access admin panel and view users', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'status' => true,
    ]);

    $response = $this->actingAs($admin)->get(route('admin.users.index'));

    $response->assertStatus(200);
    $response->assertSee('Kelola User Global');
});

test('regular user cannot access admin panel', function () {
    $user = User::factory()->create([
        'role' => UserRole::USER->value,
        'status' => true,
    ]);

    $response = $this->actingAs($user)->get(route('admin.users.index'));

    $response->assertStatus(403);
});

test('admin can create a new user', function () {
    $admin = User::factory()->create([
        'role' => UserRole::ADMIN->value,
        'status' => true,
    ]);

    $response = $this->actingAs($admin)->post(route('admin.users.store'), [
        'name' => 'Budi Worker',
        'email' => 'budi@jara.com',
        'password' => 'password123',
        'role' => 'user',
        'status' => 1,
    ]);

    $response->assertRedirect(route('admin.users.index'));
    $this->assertDatabaseHas('users', ['email' => 'budi@jara.com']);
});
