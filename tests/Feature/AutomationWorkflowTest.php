<?php

use App\Enums\AutomationAction;
use App\Enums\AutomationTrigger;
use App\Enums\TaskPriority;
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

test('automation engine respects the per subject cooldown', function () {
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
        'cooldown_minutes' => 60,
        'is_active' => true,
        'created_by' => $operator->id,
    ]);

    $first = app(AutomationEngine::class)->run();

    Carbon::setTestNow('2026-08-30 12:30:00');
    $second = app(AutomationEngine::class)->run();

    Carbon::setTestNow('2026-08-30 13:01:00');
    $third = app(AutomationEngine::class)->run();

    expect($first['executed'])->toBe(1)
        ->and($second['executed'])->toBe(0)
        ->and($second['skipped'])->toBeGreaterThanOrEqual(1)
        ->and($third['executed'])->toBe(1);

    Carbon::setTestNow();
});

test('automation can assign an unassigned task to a selected user', function () {
    $manager = automationWorkflowUser('manager');
    $target = automationWorkflowUser('operator');
    $project = automationWorkflowProject($manager);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => null,
        'status' => TaskStatus::Todo,
        'created_by' => $manager->id,
    ]);

    AutomationRule::query()->create([
        'name' => 'Assign orphan tasks',
        'trigger' => AutomationTrigger::TaskUnassigned,
        'action' => AutomationAction::AssignUser,
        'action_config' => ['user_id' => $target->id],
        'cooldown_minutes' => 15,
        'is_active' => true,
        'created_by' => $manager->id,
    ]);

    $summary = app(AutomationEngine::class)->run();

    expect($summary['executed'])->toBe(1)
        ->and($task->fresh()->assigned_to)->toBe($target->id);
});

test('automation can raise the priority of an unassigned task', function () {
    $manager = automationWorkflowUser('manager');
    $project = automationWorkflowProject($manager);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => null,
        'status' => TaskStatus::Todo,
        'priority' => TaskPriority::Low,
        'created_by' => $manager->id,
    ]);

    AutomationRule::query()->create([
        'name' => 'Escalate orphan tasks',
        'trigger' => AutomationTrigger::TaskUnassigned,
        'action' => AutomationAction::SetTaskPriority,
        'action_config' => ['priority' => TaskPriority::Urgent->value],
        'cooldown_minutes' => 15,
        'is_active' => true,
        'created_by' => $manager->id,
    ]);

    app(AutomationEngine::class)->run();

    expect($task->fresh()->priority)->toBe(TaskPriority::Urgent);
});

test('automation preview reports eligible subjects without changing them', function () {
    $manager = automationWorkflowUser('manager');
    $target = automationWorkflowUser('operator');
    $project = automationWorkflowProject($manager);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => null,
        'status' => TaskStatus::Todo,
        'created_by' => $manager->id,
    ]);

    $rule = AutomationRule::query()->create([
        'name' => 'Preview orphan tasks',
        'trigger' => AutomationTrigger::TaskUnassigned,
        'action' => AutomationAction::AssignUser,
        'action_config' => ['user_id' => $target->id],
        'cooldown_minutes' => 60,
        'is_active' => true,
        'created_by' => $manager->id,
    ]);

    $preview = app(AutomationEngine::class)->preview($rule);

    expect($preview['matched'])->toBe(1)
        ->and($preview['eligible'])->toBe(1)
        ->and($preview['cooldown'])->toBe(0)
        ->and($task->fresh()->assigned_to)->toBeNull();
});

test('automation manager can preview pause and resume a rule', function () {
    $manager = automationWorkflowUser('manager');
    $rule = AutomationRule::query()->create([
        'name' => 'Managed rule',
        'trigger' => AutomationTrigger::ProjectDueSoon,
        'action' => AutomationAction::NotifyManager,
        'cooldown_minutes' => 1440,
        'is_active' => true,
        'created_by' => $manager->id,
    ]);

    $this->actingAs($manager)
        ->post(route('automations.preview', $rule))
        ->assertRedirect()
        ->assertSessionHas('automation_preview', fn (array $preview) => $preview['rule_id'] === $rule->id);

    $this->actingAs($manager)
        ->patch(route('automations.toggle', $rule))
        ->assertRedirect();

    expect($rule->fresh()->is_active)->toBeFalse();

    $this->actingAs($manager)
        ->patch(route('automations.toggle', $rule))
        ->assertRedirect();

    expect($rule->fresh()->is_active)->toBeTrue();
});
