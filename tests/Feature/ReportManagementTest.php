<?php

use App\Models\Project;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function reportUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

    return $user;
}

test('viewer can open reports but cannot export them', function () {
    $viewer = reportUser('viewer');

    $this->actingAs($viewer)
        ->get(route('reports.index'))
        ->assertOk();

    $this->get(route('reports.csv', ['type' => 'projects']))
        ->assertForbidden();
});

test('manager can export reports as csv and excel', function () {
    $manager = reportUser('manager');
    Project::factory()->create(['created_by' => $manager->id]);

    $this->actingAs($manager)
        ->get(route('reports.csv', ['type' => 'projects']))
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');

    $this->get(route('reports.excel', ['type' => 'projects']))
        ->assertOk()
        ->assertHeader('content-type', 'application/vnd.ms-excel; charset=UTF-8');

    $this->get(route('reports.pdf', ['type' => 'projects']))
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

test('report date range must be valid', function () {
    $administrator = reportUser('administrator');

    $this->actingAs($administrator)
        ->get(route('reports.index', [
            'date_from' => '2026-08-20',
            'date_to' => '2026-08-10',
        ]))
        ->assertSessionHasErrors('date_to');
});
