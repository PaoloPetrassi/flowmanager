<?php

use App\Enums\AssetStatus;
use App\Models\Asset;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAssetTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

function validAssetPayload(Company $company, array $overrides = []): array
{
    return array_merge([
        'company_id' => $company->id,
        'assigned_to' => null,
        'asset_tag' => 'AST-TEST-001',
        'name' => 'Test notebook',
        'category' => 'Computer',
        'brand' => 'Example Brand',
        'model' => 'MODEL-1',
        'serial_number' => 'SN-TEST-001',
        'status' => AssetStatus::Available->value,
        'purchase_date' => '2026-01-10',
        'purchase_cost' => '1500.00',
        'warranty_expires_at' => '2029-01-10',
        'notes' => 'Asset used by the feature test.',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('viewer can view assets but cannot create them', function () {
    $viewer = createAssetTestUser('viewer');
    $company = Company::factory()->create(['created_by' => $viewer->id]);
    $asset = Asset::factory()->create([
        'company_id' => $company->id,
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)->get(route('assets.index'))->assertOk()->assertSee($asset->asset_tag);
    $this->actingAs($viewer)->post(route('assets.store'), validAssetPayload($company))->assertForbidden();
});

test('manager can create and update an asset', function () {
    $manager = createAssetTestUser('manager');
    $company = Company::factory()->create(['created_by' => $manager->id]);

    $response = $this->actingAs($manager)
        ->post(route('assets.store'), validAssetPayload($company));

    $asset = Asset::query()->where('asset_tag', 'AST-TEST-001')->firstOrFail();
    $response->assertRedirect(route('assets.show', $asset));

    $this->actingAs($manager)
        ->put(route('assets.update', $asset), validAssetPayload($company, [
            'name' => 'Updated notebook',
            'status' => AssetStatus::Maintenance->value,
        ]))
        ->assertRedirect(route('assets.show', $asset));

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'name' => 'Updated notebook',
        'status' => AssetStatus::Maintenance->value,
    ]);
});

test('operator can assign and unassign an asset', function () {
    $operator = createAssetTestUser('operator');
    $assignee = User::factory()->create();
    $company = Company::factory()->create(['created_by' => $operator->id]);
    $asset = Asset::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => null,
        'status' => AssetStatus::Available,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->put(route('assets.assignment.update', $asset), ['assigned_to' => $assignee->id])
        ->assertRedirect(route('assets.show', $asset));

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => $assignee->id,
        'status' => AssetStatus::Assigned->value,
    ]);

    $this->actingAs($operator)
        ->put(route('assets.assignment.update', $asset), ['assigned_to' => null])
        ->assertRedirect(route('assets.show', $asset));

    $this->assertDatabaseHas('assets', [
        'id' => $asset->id,
        'assigned_to' => null,
        'status' => AssetStatus::Available->value,
    ]);
});

test('administrator can soft delete an asset', function () {
    $administrator = createAssetTestUser('administrator');
    $company = Company::factory()->create(['created_by' => $administrator->id]);
    $asset = Asset::factory()->create([
        'company_id' => $company->id,
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->delete(route('assets.destroy', $asset))
        ->assertRedirect(route('assets.index'));

    $this->assertSoftDeleted('assets', ['id' => $asset->id]);
});
