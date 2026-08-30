<?php

use App\Enums\TaskPriority;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createTaskTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $role = Role::query()->where('slug', $roleSlug)->firstOrFail();
    $user->roles()->attach($role);

    return $user;
}

function validTaskPayload(Project $project, array $overrides = []): array
{
    return array_merge([
        'project_id' => $project->id,
        'assigned_to' => null,
        'title' => 'Prepare project delivery',
        'status' => TaskStatus::Todo->value,
        'priority' => TaskPriority::Medium->value,
        'due_date' => '2026-10-15',
        'description' => 'Feature test task.',
    ], $overrides);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('viewer can view tasks but cannot create them', function () {
    $viewer = createTaskTestUser('viewer');
    $company = Company::factory()->create(['created_by' => $viewer->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $viewer->id,
        'created_by' => $viewer->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $viewer->id,
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)->get(route('tasks.index'))->assertOk()->assertSee($task->title);
    $this->actingAs($viewer)->post(route('tasks.store'), validTaskPayload($project))->assertForbidden();
});

test('operator can create and update tasks', function () {
    $operator = createTaskTestUser('operator');
    $company = Company::factory()->create(['created_by' => $operator->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $operator->id,
        'created_by' => $operator->id,
    ]);

    $response = $this->actingAs($operator)
        ->post(route('tasks.store'), validTaskPayload($project, ['assigned_to' => $operator->id]));

    $task = Task::query()->where('title', 'Prepare project delivery')->firstOrFail();
    $response->assertRedirect(route('tasks.show', $task));

    $this->actingAs($operator)
        ->put(route('tasks.update', $task), validTaskPayload($project, [
            'title' => 'Complete delivery',
            'status' => TaskStatus::InProgress->value,
            'priority' => TaskPriority::High->value,
        ]))
        ->assertRedirect(route('tasks.show', $task));

    $this->assertDatabaseHas('tasks', [
        'id' => $task->id,
        'title' => 'Complete delivery',
        'status' => TaskStatus::InProgress->value,
    ]);
});

test('completing a task records the completion timestamp', function () {
    $operator = createTaskTestUser('operator');
    $company = Company::factory()->create(['created_by' => $operator->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $operator->id,
        'created_by' => $operator->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'status' => TaskStatus::Todo,
        'completed_at' => null,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->put(route('tasks.update', $task), validTaskPayload($project, [
            'status' => TaskStatus::Completed->value,
        ]))
        ->assertRedirect(route('tasks.show', $task));

    expect($task->fresh()->completed_at)->not->toBeNull();
});

test('administrator can soft delete a task', function () {
    $administrator = createTaskTestUser('administrator');
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
        ->delete(route('tasks.destroy', $task))
        ->assertRedirect(route('tasks.index'));

    $this->assertSoftDeleted('tasks', ['id' => $task->id]);
});
