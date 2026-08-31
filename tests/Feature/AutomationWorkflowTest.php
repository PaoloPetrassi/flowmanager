<?php

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use App\Enums\TaskRecurrence;
use App\Enums\TaskStatus;
use App\Enums\TicketPriority;
use App\Models\AutomationRule;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use App\Services\AutomationEngine;
use App\Services\ReminderService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Notification;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function automationWorkflowUser(string $roleSlug = 'operator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

    return $user;
}

function automationWorkflowProject(User $user): Project
{
    $company = Company::factory()->create(['created_by' => $user->id]);

    return Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $user->id,
        'created_by' => $user->id,
    ]);
}

test('ticket receives an SLA deadline based on priority', function () {
    Carbon::setTestNow('2026-08-30 10:00:00');
    $user = automationWorkflowUser();

    $ticket = Ticket::factory()->create([
        'assigned_to' => $user->id,
        'priority' => TicketPriority::Urgent,
        'sla_due_at' => null,
        'created_by' => $user->id,
    ]);

    expect($ticket->fresh()->sla_due_at?->format('Y-m-d H:i:s'))->toBe('2026-08-30 14:00:00');

    Carbon::setTestNow();
});

test('completing a recurring task creates the next occurrence from any workflow entry point', function () {
    $operator = automationWorkflowUser();
    $project = automationWorkflowProject($operator);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'status' => TaskStatus::Todo,
        'due_date' => '2026-09-01',
        'recurrence' => TaskRecurrence::Weekly,
        'recurrence_interval' => 1,
        'recurrence_ends_at' => '2026-10-01',
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->patch(route('boards.tasks.status', $task), ['status' => TaskStatus::Completed->value])
        ->assertRedirect();

    $task->refresh();
    $next = $task->nextRecurrence;

    expect($next)->not->toBeNull()
        ->and($next->due_date?->format('Y-m-d'))->toBe('2026-09-08')
        ->and($next->status)->toBe(TaskStatus::Todo);
});

test('open dependencies prevent a task from being completed', function () {
    $operator = automationWorkflowUser();
    $project = automationWorkflowProject($operator);
    $dependency = Task::factory()->create([
        'project_id' => $project->id,
        'status' => TaskStatus::InProgress,
        'created_by' => $operator->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'status' => TaskStatus::Todo,
        'created_by' => $operator->id,
    ]);
    $task->dependencies()->attach($dependency);

    $this->actingAs($operator)
        ->patch(route('boards.tasks.status', $task), ['status' => TaskStatus::Completed->value])
        ->assertSessionHasErrors('status');

    expect($task->fresh()->status)->toBe(TaskStatus::Todo);
});

test('reminder service notifies task assignees and marks reminders as sent', function () {
    Notification::fake();
    Carbon::setTestNow('2026-08-30 10:00:00');
    $operator = automationWorkflowUser();
    $project = automationWorkflowProject($operator);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'status' => TaskStatus::Todo,
        'due_date' => '2026-08-31',
        'due_reminder_sent_at' => null,
        'created_by' => $operator->id,
    ]);

    $summary = app(ReminderService::class)->run();

    expect($summary['tasks'])->toBe(1)
        ->and($task->fresh()->due_reminder_sent_at)->not->toBeNull();
    Notification::assertSentTo($operator, App\Notifications\FlowNotification::class);

    Carbon::setTestNow();
});

test('automation engine executes an overdue task rule only once per day', function () {
    Notification::fake();
    Carbon::setTestNow('2026-08-30 12:00:00');
    $operator = automationWorkflowUser();
    $project = automationWorkflowProject($operator);
    Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'status' => TaskStatus::InProgress,
        'due_date' => '2026-08-29',
        'created_by' => $operator->id,
    ]);

    AutomationRule::query()->create([
        'name' => 'Overdue task alert',
        'trigger' => AutomationTrigger::TaskOverdue,
        'action' => AutomationAction::NotifyAssignee,
        'is_active' => true,
        'created_by' => $operator->id,
    ]);

    $first = app(AutomationEngine::class)->run();
    $second = app(AutomationEngine::class)->run();

    expect($first['executed'])->toBe(1)
        ->and($second['executed'])->toBe(0)
        ->and($second['skipped'])->toBeGreaterThanOrEqual(1);

    Carbon::setTestNow();
});
