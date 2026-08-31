<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createUserManagementTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('manager can view users but cannot create them', function () {
    $manager = createUserManagementTestUser('manager');

    $this->actingAs($manager)
        ->get(route('users.index'))
        ->assertOk()
        ->assertSee($manager->email);

    $this->actingAs($manager)
        ->post(route('users.store'), [
            'name' => 'New User',
            'email' => 'new.user@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [],
        ])
        ->assertForbidden();
});

test('administrator can create a user and assign a role', function () {
    $administrator = createUserManagementTestUser('administrator');
    $operatorRole = Role::query()->where('slug', 'operator')->firstOrFail();

    $response = $this->actingAs($administrator)
        ->post(route('users.store'), [
            'name' => 'Operations User',
            'email' => 'operations@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'roles' => [$operatorRole->id],
        ]);

    $user = User::query()->where('email', 'operations@example.test')->firstOrFail();
    $response->assertRedirect(route('users.show', $user));

    expect($user->hasRole('operator'))->toBeTrue();
});

test('administrator can update user details and roles', function () {
    $administrator = createUserManagementTestUser('administrator');
    $user = User::factory()->create(['email' => 'before@example.test']);
    $viewerRole = Role::query()->where('slug', 'viewer')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('users.update', $user), [
            'name' => 'Updated User',
            'email' => 'after@example.test',
            'password' => '',
            'password_confirmation' => '',
            'roles' => [$viewerRole->id],
        ])
        ->assertRedirect(route('users.show', $user));

    $this->assertDatabaseHas('users', [
        'id' => $user->id,
        'name' => 'Updated User',
        'email' => 'after@example.test',
    ]);

    expect($user->fresh()->hasRole('viewer'))->toBeTrue();
});

test('administrator cannot delete their own account', function () {
    $administrator = createUserManagementTestUser('administrator');

    $this->actingAs($administrator)
        ->delete(route('users.destroy', $administrator))
        ->assertForbidden();

    $this->assertDatabaseHas('users', ['id' => $administrator->id]);
});

test('administrator can delete another account', function () {
    $administrator = createUserManagementTestUser('administrator');
    $user = User::factory()->create();

    $this->actingAs($administrator)
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'));

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('last administrator cannot lose the administrator role', function () {
    $administrator = createUserManagementTestUser('administrator');
    $viewerRole = Role::query()->where('slug', 'viewer')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('users.update', $administrator), [
            'name' => $administrator->name,
            'email' => $administrator->email,
            'password' => '',
            'password_confirmation' => '',
            'roles' => [$viewerRole->id],
        ])
        ->assertSessionHasErrors('roles');

    expect($administrator->fresh()->hasRole('administrator'))->toBeTrue();
});
