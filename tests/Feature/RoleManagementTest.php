<?php

use App\Models\Permission;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createRoleManagementTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('manager can view roles but cannot create them', function () {
    $manager = createRoleManagementTestUser('manager');

    $this->actingAs($manager)
        ->get(route('roles.index'))
        ->assertOk()
        ->assertSee('Administrator');

    $this->actingAs($manager)
        ->post(route('roles.store'), [
            'name' => 'Custom role',
            'slug' => 'custom-role',
            'description' => 'Test role.',
            'permissions' => [],
        ])
        ->assertForbidden();
});

test('administrator can create a custom role with permissions', function () {
    $administrator = createRoleManagementTestUser('administrator');
    $viewCompanies = Permission::query()->where('slug', 'companies.view')->firstOrFail();

    $response = $this->actingAs($administrator)
        ->post(route('roles.store'), [
            'name' => 'CRM Reader',
            'slug' => 'crm-reader',
            'description' => 'Read CRM records.',
            'permissions' => [$viewCompanies->id],
        ]);

    $role = Role::query()->where('slug', 'crm-reader')->firstOrFail();
    $response->assertRedirect(route('roles.show', $role));

    expect($role->permissions()->whereKey($viewCompanies->id)->exists())->toBeTrue();
    expect($role->is_system)->toBeFalse();
});

test('administrator can update permissions on a system role without changing its identity', function () {
    $administrator = createRoleManagementTestUser('administrator');
    $viewer = Role::query()->where('slug', 'viewer')->firstOrFail();
    $viewCompanies = Permission::query()->where('slug', 'companies.view')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('roles.update', $viewer), [
            'name' => 'Renamed viewer',
            'slug' => 'renamed-viewer',
            'description' => 'Updated viewer description.',
            'permissions' => [$viewCompanies->id],
        ])
        ->assertRedirect(route('roles.show', $viewer));

    $viewer->refresh();
    expect($viewer->name)->toBe('Viewer');
    expect($viewer->slug)->toBe('viewer');
    expect($viewer->description)->toBe('Updated viewer description.');
});

test('administrator cannot delete a system role', function () {
    $administrator = createRoleManagementTestUser('administrator');
    $viewer = Role::query()->where('slug', 'viewer')->firstOrFail();

    $this->actingAs($administrator)
        ->delete(route('roles.destroy', $viewer))
        ->assertForbidden();

    $this->assertDatabaseHas('roles', ['id' => $viewer->id]);
});

test('administrator can delete a custom role', function () {
    $administrator = createRoleManagementTestUser('administrator');
    $role = Role::query()->create([
        'name' => 'Temporary',
        'slug' => 'temporary',
        'description' => 'Temporary role.',
        'is_system' => false,
    ]);

    $this->actingAs($administrator)
        ->delete(route('roles.destroy', $role))
        ->assertRedirect(route('roles.index'));

    $this->assertDatabaseMissing('roles', ['id' => $role->id]);
});


test('administrator role cannot be edited through the role module', function () {
    $administrator = createRoleManagementTestUser('administrator');
    $administratorRole = Role::query()->where('slug', 'administrator')->firstOrFail();

    $this->actingAs($administrator)
        ->put(route('roles.update', $administratorRole), [
            'name' => 'Changed administrator',
            'slug' => 'changed-administrator',
            'description' => 'This change must be blocked.',
            'permissions' => [],
        ])
        ->assertForbidden();

    $administratorRole->refresh();
    expect($administratorRole->slug)->toBe('administrator');
});
