<?php

use App\Enums\CompanyStatus;
use App\Enums\CompanyType;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

/**
 * Create a user and assign the requested system role.
 */
function createCompanyTestUser(string $roleSlug): User
{
    $user = User::factory()->create();

    $role = Role::query()
        ->where('slug', $roleSlug)
        ->firstOrFail();

    $user->roles()->attach($role);

    return $user;
}

/**
 * Return a complete valid company payload.
 *
 * @return array<string, mixed>
 */
function validCompanyPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Acme Corporation',
        'legal_name' => 'Acme Corporation S.p.A.',
        'type' => CompanyType::Customer->value,
        'status' => CompanyStatus::Active->value,
        'vat_number' => 'IT12345678901',
        'tax_code' => '12345678901',
        'email' => 'info@acme.test',
        'phone' => '+39 06 12345678',
        'website' => 'https://www.acme.test',
        'industry' => 'Technology',
        'employees' => 125,
        'address' => 'Via Roma 1',
        'city' => 'Roma',
        'province' => 'RM',
        'postal_code' => '00100',
        'country_code' => 'IT',
        'notes' => 'Test company.',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest users cannot access the companies module', function () {
    $response = $this->get(
        route('companies.index')
    );

    $response->assertRedirect(
        route('login')
    );
});

test('viewer can view the companies list', function () {
    $viewer = createCompanyTestUser('viewer');

    $company = Company::factory()->create([
        'name' => 'Viewer Test Company',
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(route('companies.index'));

    $response
        ->assertOk()
        ->assertSee('Companies')
        ->assertSee($company->name);
});

test('viewer cannot create a company', function () {
    $viewer = createCompanyTestUser('viewer');

    $response = $this
        ->actingAs($viewer)
        ->post(
            route('companies.store'),
            validCompanyPayload()
        );

    $response->assertForbidden();

    $this->assertDatabaseMissing('companies', [
        'name' => 'Acme Corporation',
    ]);
});

test('operator can create a company', function () {
    $operator = createCompanyTestUser('operator');

    $response = $this
        ->actingAs($operator)
        ->post(
            route('companies.store'),
            validCompanyPayload()
        );

    $company = Company::query()
        ->where('name', 'Acme Corporation')
        ->firstOrFail();

    $response->assertRedirect(
        route('companies.show', $company)
    );

    $this->assertDatabaseHas('companies', [
        'name' => 'Acme Corporation',
        'type' => CompanyType::Customer->value,
        'status' => CompanyStatus::Active->value,
        'created_by' => $operator->id,
    ]);
});

test('administrator can update a company', function () {
    $administrator = createCompanyTestUser(
        'administrator'
    );

    $company = Company::factory()->create([
        'name' => 'Old Company Name',
        'created_by' => $administrator->id,
    ]);

    $response = $this
        ->actingAs($administrator)
        ->put(
            route('companies.update', $company),
            validCompanyPayload([
                'name' => 'Updated Company Name',
            ])
        );

    $response->assertRedirect(
        route('companies.show', $company)
    );

    $this->assertDatabaseHas('companies', [
        'id' => $company->id,
        'name' => 'Updated Company Name',
    ]);
});

test('administrator can soft delete a company', function () {
    $administrator = createCompanyTestUser(
        'administrator'
    );

    $company = Company::factory()->create([
        'created_by' => $administrator->id,
    ]);

    $response = $this
        ->actingAs($administrator)
        ->delete(
            route('companies.destroy', $company)
        );

    $response->assertRedirect(
        route('companies.index')
    );

    $this->assertSoftDeleted('companies', [
        'id' => $company->id,
    ]);
});

test('duplicate vat numbers are rejected', function () {
    $administrator = createCompanyTestUser(
        'administrator'
    );

    Company::factory()->create([
        'vat_number' => 'IT12345678901',
        'created_by' => $administrator->id,
    ]);

    $response = $this
        ->actingAs($administrator)
        ->from(route('companies.create'))
        ->post(
            route('companies.store'),
            validCompanyPayload([
                'tax_code' => '99999999999',
            ])
        );

    $response
        ->assertRedirect(
            route('companies.create')
        )
        ->assertSessionHasErrors([
            'vat_number',
        ]);

    expect(
        Company::query()
            ->where('vat_number', 'IT12345678901')
            ->count()
    )->toBe(1);
});

test('companies can be searched by name', function () {
    $viewer = createCompanyTestUser('viewer');

    Company::factory()->create([
        'name' => 'Acme Search Target',
        'legal_name' => 'Acme Search Target S.p.A.',
        'created_by' => $viewer->id,
    ]);

    Company::factory()->create([
        'name' => 'Completely Different Company',
        'legal_name' => 'Completely Different Company S.r.l.',
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(
            route('companies.index', [
                'search' => 'Acme Search',
            ])
        );

    $response
        ->assertOk()
        ->assertSee('Acme Search Target')
        ->assertDontSee('Completely Different Company');
});

test('companies can be filtered by type and status', function () {
    $viewer = createCompanyTestUser('viewer');

    Company::factory()->create([
        'name' => 'Active Customer',
        'type' => CompanyType::Customer,
        'status' => CompanyStatus::Active,
        'created_by' => $viewer->id,
    ]);

    Company::factory()->create([
        'name' => 'Suspended Supplier',
        'type' => CompanyType::Supplier,
        'status' => CompanyStatus::Suspended,
        'created_by' => $viewer->id,
    ]);

    $response = $this
        ->actingAs($viewer)
        ->get(
            route('companies.index', [
                'type' => CompanyType::Customer->value,
                'status' => CompanyStatus::Active->value,
            ])
        );

    $response
        ->assertOk()
        ->assertSee('Active Customer')
        ->assertDontSee('Suspended Supplier');
});
