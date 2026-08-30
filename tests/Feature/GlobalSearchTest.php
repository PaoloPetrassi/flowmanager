<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createSearchTestUser(string $roleSlug): User
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

test('global search finds records across accessible modules', function () {
    $operator = createSearchTestUser('operator');
    $company = Company::factory()->create([
        'name' => 'Orion Logistics',
        'created_by' => $operator->id,
    ]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'subject' => 'Orion network issue',
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->get(route('search.index', ['q' => 'Orion']))
        ->assertOk()
        ->assertSee('Orion Logistics')
        ->assertSee('Orion network issue')
        ->assertSee($ticket->reference);
});

test('global search does not expose modules the user cannot view', function () {
    $viewer = createSearchTestUser('viewer');
    $administrator = createSearchTestUser('administrator');
    $administrator->update([
        'name' => 'Sensitive Administrator',
        'email' => 'sensitive-admin@example.test',
    ]);

    $this->actingAs($viewer)
        ->get(route('search.index', ['q' => 'Sensitive']))
        ->assertOk()
        ->assertDontSee('Sensitive Administrator')
        ->assertDontSee('sensitive-admin@example.test');
});

test('global search requires at least two characters', function () {
    $viewer = createSearchTestUser('viewer');

    $this->actingAs($viewer)
        ->get(route('search.index', ['q' => 'A']))
        ->assertOk()
        ->assertSee('Enter at least 2 characters');
});
