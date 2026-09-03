<?php

use App\Models\Asset;
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

test('global search can be restricted to one accessible module', function () {
    $operator = createSearchTestUser('operator');
    Company::factory()->create([
        'name' => 'ScopeTarget Holdings',
        'created_by' => $operator->id,
    ]);
    Ticket::factory()->create([
        'company_id' => null,
        'subject' => 'ScopeTarget support request',
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->get(route('search.index', ['q' => 'ScopeTarget', 'scope' => 'tickets']))
        ->assertOk()
        ->assertSee('ScopeTarget support request')
        ->assertDontSee('ScopeTarget Holdings');
});

test('command palette searches assets with metadata', function () {
    $viewer = createSearchTestUser('viewer');
    $asset = Asset::factory()->create([
        'name' => 'Nebula Workstation',
        'asset_tag' => 'AST-NEBULA',
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)
        ->getJson(route('command-palette', ['q' => 'Nebula']))
        ->assertOk()
        ->assertJsonFragment([
            'label' => 'Nebula Workstation',
            'type' => __('Assets'),
        ])
        ->assertJsonFragment([
            'url' => route('assets.show', $asset),
        ]);
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
