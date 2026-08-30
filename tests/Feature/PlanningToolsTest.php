<?php

use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Asset;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function planningUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

    return $user;
}

test('calendar combines project task and warranty deadlines', function () {
    $viewer = planningUser('viewer');
    $project = Project::factory()->create([
        'due_date' => '2026-08-15',
        'created_by' => $viewer->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'due_date' => '2026-08-16',
        'created_by' => $viewer->id,
    ]);
    $asset = Asset::factory()->create([
        'warranty_expires_at' => '2026-08-17',
        'created_by' => $viewer->id,
    ]);

    $this->actingAs($viewer)
        ->get(route('calendar.index', ['month' => '2026-08']))
        ->assertOk()
        ->assertSee($project->name)
        ->assertSee($task->title)
        ->assertSee($asset->name);
});

test('viewer can read the kanban but cannot move a task', function () {
    $viewer = planningUser('viewer');
    $task = Task::factory()->create(['status' => TaskStatus::Todo]);

    $this->actingAs($viewer)
        ->get(route('boards.index', ['type' => 'tasks']))
        ->assertOk()
        ->assertSee($task->title);

    $this->patch(route('boards.tasks.status', $task), [
        'status' => TaskStatus::Completed->value,
    ])->assertForbidden();
});

test('operator can move tasks and tickets from the kanban', function () {
    $operator = planningUser('operator');
    $task = Task::factory()->create([
        'status' => TaskStatus::Todo,
        'created_by' => $operator->id,
    ]);
    $ticket = Ticket::factory()->create([
        'status' => TicketStatus::Open,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->patch(route('boards.tasks.status', $task), [
            'status' => TaskStatus::Completed->value,
        ])
        ->assertRedirect();

    expect($task->fresh()->status)->toBe(TaskStatus::Completed)
        ->and($task->fresh()->completed_at)->not->toBeNull();

    $this->patch(route('boards.tickets.status', $ticket), [
        'status' => TicketStatus::Resolved->value,
    ])->assertRedirect();

    expect($ticket->fresh()->status)->toBe(TicketStatus::Resolved)
        ->and($ticket->fresh()->resolved_at)->not->toBeNull();
});
