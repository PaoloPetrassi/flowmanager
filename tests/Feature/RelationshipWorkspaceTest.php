<?php

use App\Models\Asset;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Role;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function createRelationshipWorkspaceUser(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(
        Role::query()->where('slug', 'administrator')->firstOrFail()
    );

    return $user;
}

test('company workspace shows linked projects assets and tickets', function () {
    $user = createRelationshipWorkspaceUser();
    $company = Company::factory()->create(['created_by' => $user->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $user->id,
        'created_by' => $user->id,
    ]);
    $asset = Asset::factory()->create([
        'company_id' => $company->id,
        'created_by' => $user->id,
    ]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('companies.show', $company))
        ->assertOk()
        ->assertSee($project->name)
        ->assertSee($asset->name)
        ->assertSee($ticket->subject);
});

test('contact workspace shows linked projects and tickets', function () {
    $user = createRelationshipWorkspaceUser();
    $company = Company::factory()->create(['created_by' => $user->id]);
    $contact = Contact::factory()->create([
        'company_id' => $company->id,
        'created_by' => $user->id,
    ]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'contact_id' => $contact->id,
        'manager_id' => $user->id,
        'created_by' => $user->id,
    ]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'contact_id' => $contact->id,
        'created_by' => $user->id,
    ]);

    $this->actingAs($user)
        ->get(route('contacts.show', $contact))
        ->assertOk()
        ->assertSee($project->name)
        ->assertSee($ticket->subject);
});
