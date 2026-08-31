<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTrashTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(
        Role::query()->where('slug', $roleSlug)->firstOrFail()
    );

    return $user;
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('administrator can see and restore a soft deleted record', function () {
    $administrator = createTrashTestUser('administrator');
    $company = Company::factory()->create([
        'name' => 'Recoverable Company',
        'created_by' => $administrator->id,
    ]);
    $company->delete();

    $this->actingAs($administrator)
        ->get(route('trash.index'))
        ->assertOk()
        ->assertSee('Recoverable Company');

    $this->patch(route('trash.restore', ['company', $company->id]))
        ->assertRedirect();

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'deleted_at' => null,
    ]);
});

test('administrator can permanently delete an independent trashed record', function () {
    $administrator = createTrashTestUser('administrator');
    $asset = Asset::factory()->create([
        'created_by' => $administrator->id,
    ]);
    $asset->delete();

    $this->actingAs($administrator)
        ->delete(route('trash.destroy', ['asset', $asset->id]))
        ->assertRedirect();

    $this->assertDatabaseMissing('assets', [
        'id' => $asset->id,
    ]);
});

test('manager can restore trash but cannot permanently delete it', function () {
    $manager = createTrashTestUser('manager');
    $asset = Asset::factory()->create([
        'created_by' => $manager->id,
    ]);
    $asset->delete();

    $this->actingAs($manager)
        ->delete(route('trash.destroy', ['asset', $asset->id]))
        ->assertForbidden();

    $this->patch(route('trash.restore', ['asset', $asset->id]))
        ->assertRedirect();

    expect(Asset::query()->find($asset->id))->not->toBeNull();
});

test('viewer cannot access trash', function () {
    $viewer = createTrashTestUser('viewer');

    $this->actingAs($viewer)
        ->get(route('trash.index'))
        ->assertForbidden();
});


test('trash routes cannot permanently delete an active record', function () {
    $administrator = createTrashTestUser('administrator');
    $asset = Asset::factory()->create([
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->delete(route('trash.destroy', ['asset', $asset->id]))
        ->assertNotFound();

    expect(Asset::query()->find($asset->id))->not->toBeNull();
});

test('company permanent deletion is blocked while projects remain linked', function () {
    $administrator = createTrashTestUser('administrator');
    $company = Company::factory()->create([
        'created_by' => $administrator->id,
    ]);
    Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $administrator->id,
        'created_by' => $administrator->id,
    ]);
    $company->delete();

    $this->actingAs($administrator)
        ->from(route('trash.index'))
        ->delete(route('trash.destroy', ['company', $company->id]))
        ->assertRedirect(route('trash.index'))
        ->assertSessionHas('error');

    expect(Company::withTrashed()->find($company->id))->not->toBeNull();
});
