<?php

use App\Enums\CompanyStatus;
use App\Enums\CompanyType;
use App\Models\AuditLog;
use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createAuditTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(
        Role::query()->where('slug', $roleSlug)->firstOrFail()
    );

    return $user;
}

function auditCompanyPayload(array $overrides = []): array
{
    return array_merge([
        'name' => 'Audit Company',
        'legal_name' => 'Audit Company S.r.l.',
        'type' => CompanyType::Customer->value,
        'status' => CompanyStatus::Active->value,
        'vat_number' => null,
        'tax_code' => null,
        'email' => 'audit@example.test',
        'phone' => null,
        'website' => null,
        'industry' => 'Technology',
        'employees' => 10,
        'address' => null,
        'city' => 'Roma',
        'province' => 'RM',
        'postal_code' => '00100',
        'country_code' => 'IT',
        'notes' => null,
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('updating a record creates an audit entry with before and after values', function () {
    $administrator = createAuditTestUser('administrator');
    $company = Company::factory()->create([
        'name' => 'Original Company',
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->put(route('companies.update', $company), auditCompanyPayload([
            'name' => 'Updated Company',
        ]))
        ->assertRedirect(route('companies.show', $company));

    $log = AuditLog::query()
        ->where('auditable_type', Company::class)
        ->where('auditable_id', $company->id)
        ->where('event', 'updated')
        ->latest('id')
        ->firstOrFail();

    expect($log->user_id)->toBe($administrator->id);
    expect($log->old_values['name'])->toBe('Original Company');
    expect($log->new_values['name'])->toBe('Updated Company');
});

test('manager can view activity log while viewer cannot', function () {
    $manager = createAuditTestUser('manager');
    $viewer = createAuditTestUser('viewer');

    $this->actingAs($manager)
        ->get(route('activity.index'))
        ->assertOk();

    $this->actingAs($viewer)
        ->get(route('activity.index'))
        ->assertForbidden();
});

test('record workspace shows recent audit activity to authorized users', function () {
    $manager = createAuditTestUser('manager');
    $this->actingAs($manager);

    $company = Company::factory()->create([
        'name' => 'Tracked Company',
        'created_by' => $manager->id,
    ]);

    $company->update(['name' => 'Tracked Company Updated']);

    $this->get(route('companies.show', $company))
        ->assertOk()
        ->assertSee('Recent activity')
        ->assertSee('Tracked Company Updated');
});
