<?php

use App\Enums\ProjectPriority;
use App\Enums\ProjectStatus;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createProjectTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

function validProjectPayload(Company $company, array $overrides = []): array
{
    return array_merge([
        'company_id' => $company->id,
        'contact_id' => null,
        'manager_id' => null,
        'code' => 'PRJ-TEST-001',
        'name' => 'Test implementation project',
        'status' => ProjectStatus::Active->value,
        'priority' => ProjectPriority::High->value,
        'start_date' => '2026-09-01',
        'due_date' => '2026-12-31',
        'budget' => '25000.00',
        'description' => 'Project used by the feature test.',
        'notes' => 'Internal test note.',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('guest users cannot access the projects module', function () {
    $this->get(route('projects.index'))->assertRedirect(route('login'));
});

test('viewer can view the projects list', function () {
    $viewer = createProjectTestUser('viewer');
    $company = Company::factory()->create(['created_by' => $viewer->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $viewer->id,
        'name' => 'Visible project',
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)
        ->get(route('projects.index'))
        ->assertOk()
        ->assertSee($project->name);
});

test('operator cannot create a project', function () {
    $operator = createProjectTestUser('operator');
    $company = Company::factory()->create(['created_by' => $operator->id]);

    $this->actingAs($operator)
        ->post(route('projects.store'), validProjectPayload($company))
        ->assertForbidden();

    $this->assertDatabaseMissing('projects', ['code' => 'PRJ-TEST-001']);
});

test('manager can create and update a project', function () {
    $manager = createProjectTestUser('manager');
    $company = Company::factory()->create(['created_by' => $manager->id]);

    $response = $this->actingAs($manager)
        ->post(route('projects.store'), validProjectPayload($company));

    $project = Project::query()->where('code', 'PRJ-TEST-001')->firstOrFail();
    $response->assertRedirect(route('projects.show', $project));

    $this->actingAs($manager)
        ->put(route('projects.update', $project), validProjectPayload($company, [
            'name' => 'Updated project',
            'priority' => ProjectPriority::Urgent->value,
        ]))
        ->assertRedirect(route('projects.show', $project));

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Updated project',
        'priority' => ProjectPriority::Urgent->value,
    ]);
});

test('project contact must belong to the selected company', function () {
    $manager = createProjectTestUser('manager');
    $company = Company::factory()->create(['created_by' => $manager->id]);
    $otherCompany = Company::factory()->create(['created_by' => $manager->id]);
    $contact = Contact::factory()->create([
        'company_id' => $otherCompany->id,
        'created_by' => $manager->id,
    ]);

    $this->actingAs($manager)
        ->post(route('projects.store'), validProjectPayload($company, [
            'contact_id' => $contact->id,
        ]))
        ->assertSessionHasErrors('contact_id');

    $this->assertDatabaseMissing('projects', ['code' => 'PRJ-TEST-001']);
});

test('administrator can soft delete a project', function () {
    $administrator = createProjectTestUser('administrator');
    $company = Company::factory()->create(['created_by' => $administrator->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $administrator->id,
        'created_by' => $administrator->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $administrator->id,
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->delete(route('projects.destroy', $project))
        ->assertRedirect(route('projects.index'));

    $this->assertSoftDeleted('projects', ['id' => $project->id]);
    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});
