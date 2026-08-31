<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskPriority;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\TimeEntry;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function advancedProjectUser(string $roleSlug = 'administrator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

    return $user;
}

function advancedProject(User $user, array $overrides = []): Project
{
    $company = Company::factory()->create(['created_by' => $user->id]);

    return Project::factory()->create(array_merge([
        'company_id' => $company->id,
        'manager_id' => $user->id,
        'status' => ProjectStatus::Active,
        'created_by' => $user->id,
    ], $overrides));
}

test('project team members can be added and removed', function () {
    $administrator = advancedProjectUser();
    $member = User::factory()->create();
    $project = advancedProject($administrator);

    $this->actingAs($administrator)
        ->post(route('projects.team.store', $project), [
            'user_id' => $member->id,
            'role' => 'Developer',
        ])
        ->assertRedirect();

    expect($project->fresh()->teamMembers()->whereKey($member->id)->exists())->toBeTrue();

    $this->actingAs($administrator)
        ->delete(route('projects.team.destroy', [$project, $member]))
        ->assertRedirect();

    expect($project->fresh()->teamMembers()->whereKey($member->id)->exists())->toBeFalse();
});

test('milestones can be created completed and deleted', function () {
    $administrator = advancedProjectUser();
    $project = advancedProject($administrator);

    $this->actingAs($administrator)
        ->post(route('projects.milestones.store', $project), [
            'name' => 'Release candidate',
            'due_date' => '2026-10-01',
        ])
        ->assertRedirect();

    $milestone = Milestone::query()->where('project_id', $project->id)->firstOrFail();

    $this->actingAs($administrator)
        ->patch(route('projects.milestones.toggle', [$project, $milestone]))
        ->assertRedirect();

    expect($milestone->fresh()->completed_at)->not->toBeNull();

    $this->actingAs($administrator)
        ->delete(route('projects.milestones.destroy', [$project, $milestone]))
        ->assertRedirect();

    $this->assertDatabaseMissing('milestones', ['id' => $milestone->id]);
});

test('user can track time with timer and manual entries', function () {
    Carbon::setTestNow('2026-08-30 09:00:00');
    $operator = advancedProjectUser('operator');
    $project = advancedProject($operator);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)->post(route('tasks.time.start', $task))->assertRedirect();
    Carbon::setTestNow('2026-08-30 10:30:00');
    $this->actingAs($operator)->patch(route('tasks.time.stop', $task))->assertRedirect();

    expect(TimeEntry::query()->where('task_id', $task->id)->firstOrFail()->minutes)->toBe(90);

    $this->actingAs($operator)->post(route('tasks.time.store', $task), [
        'date' => '2026-08-29',
        'minutes' => 45,
        'note' => 'Review',
    ])->assertRedirect();

    expect($task->fresh()->timeEntries()->sum('minutes'))->toBe(135);
    Carbon::setTestNow();
});

test('project progress is calculated from completed tasks unless manually overridden', function () {
    $administrator = advancedProjectUser();
    $project = advancedProject($administrator);

    Task::factory()->count(3)->create([
        'project_id' => $project->id,
        'status' => TaskStatus::Todo,
        'created_by' => $administrator->id,
    ]);
    Task::factory()->create([
        'project_id' => $project->id,
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
        'created_by' => $administrator->id,
    ]);

    $project->load('tasks');
    expect($project->progressPercentage())->toBe(25);

    $project->forceFill(['progress_override' => 80])->save();
    expect($project->fresh()->progressPercentage())->toBe(80);
});

test('project can be saved as a template and instantiated', function () {
    $administrator = advancedProjectUser();
    $project = advancedProject($administrator, ['name' => 'Source project']);
    Task::factory()->create([
        'project_id' => $project->id,
        'title' => 'Template task',
        'assigned_to' => $administrator->id,
        'due_date' => '2026-10-05',
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->post(route('projects.template', $project))
        ->assertRedirect(route('project-templates.index'));

    $template = Project::query()->where('is_template', true)->firstOrFail();
    expect($template->tasks()->count())->toBe(1)
        ->and($template->tasks()->first()->assigned_to)->toBeNull()
        ->and($template->tasks()->first()->due_date)->toBeNull();

    $company = Company::factory()->create(['created_by' => $administrator->id]);

    $this->actingAs($administrator)
        ->post(route('project-templates.instantiate', $template), [
            'name' => 'Instantiated project',
            'company_id' => $company->id,
            'manager_id' => $administrator->id,
            'start_date' => '2026-10-01',
            'due_date' => '2026-12-01',
        ])
        ->assertRedirect();

    $created = Project::query()->where('name', 'Instantiated project')->firstOrFail();
    expect($created->is_template)->toBeFalse()
        ->and($created->tasks()->count())->toBe(1);
});

test('duplicating a project preserves team structure and task dependencies', function () {
    $administrator = advancedProjectUser();
    $member = User::factory()->create();
    $project = advancedProject($administrator, ['name' => 'Original delivery']);
    $project->teamMembers()->attach($member->id, ['role' => 'QA']);

    $first = Task::factory()->create([
        'project_id' => $project->id,
        'title' => 'First task',
        'created_by' => $administrator->id,
    ]);
    $second = Task::factory()->create([
        'project_id' => $project->id,
        'title' => 'Second task',
        'created_by' => $administrator->id,
    ]);
    $second->dependencies()->attach($first);

    $this->actingAs($administrator)
        ->post(route('projects.duplicate', $project))
        ->assertRedirect();

    $copy = Project::query()->where('name', 'Original delivery '.__('Copy'))->firstOrFail();
    $copiedSecond = $copy->tasks()->where('title', 'Second task')->firstOrFail();

    expect($copy->teamMembers()->whereKey($member->id)->exists())->toBeTrue()
        ->and($copiedSecond->dependencies()->count())->toBe(1);
});

test('circular parent and dependency relationships are rejected', function () {
    $operator = advancedProjectUser('operator');
    $project = advancedProject($operator);
    $parent = Task::factory()->create([
        'project_id' => $project->id,
        'title' => 'Parent',
        'created_by' => $operator->id,
    ]);
    $child = Task::factory()->create([
        'project_id' => $project->id,
        'parent_id' => $parent->id,
        'title' => 'Child',
        'created_by' => $operator->id,
    ]);
    $child->dependencies()->attach($parent);

    $payload = [
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'parent_id' => $child->id,
        'milestone_id' => null,
        'title' => $parent->title,
        'status' => TaskStatus::Todo->value,
        'priority' => TaskPriority::Medium->value,
        'recurrence' => TaskRecurrence::None->value,
        'recurrence_interval' => 1,
        'recurrence_ends_at' => null,
        'due_date' => '2026-10-15',
        'estimated_minutes' => 60,
        'dependency_ids' => [$child->id],
        'description' => null,
    ];

    $this->actingAs($operator)
        ->put(route('tasks.update', $parent), $payload)
        ->assertSessionHasErrors(['parent_id', 'dependency_ids']);
});

test('gantt and workload views are available according to permissions', function () {
    $viewer = advancedProjectUser('viewer');
    $manager = advancedProjectUser('manager');

    $this->actingAs($viewer)->get(route('planning.gantt'))->assertOk();
    $this->actingAs($viewer)->get(route('planning.workload'))->assertForbidden();
    $this->actingAs($manager)->get(route('planning.workload'))->assertOk();
});
