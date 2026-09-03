<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use App\Notifications\FlowNotification;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function createNotificationTestUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(
        Role::query()->where('slug', $roleSlug)->firstOrFail()
    );

    return $user;
}

function createNotificationTestProject(User $manager): Project
{
    $company = Company::factory()->create(['created_by' => $manager->id]);

    return Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $manager->id,
        'created_by' => $manager->id,
    ]);
}

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('assigning a task to another user creates a categorized database notification', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $project = createNotificationTestProject($manager);

    $this->actingAs($manager);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $manager->id,
    ]);

    $notification = $operator->notifications()->firstOrFail();

    expect($notification->data['title_key'])->toBe('Task assigned to you')
        ->and($notification->data['category'])->toBe('assignments')
        ->and($notification->data['route_name'])->toBe('tasks.show')
        ->and($notification->data['route_parameters'])->toBe([$task->id]);
});

test('opening a notification marks it as read and redirects to its record', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $project = createNotificationTestProject($manager);

    $this->actingAs($manager);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $manager->id,
    ]);

    $notification = $operator->notifications()->firstOrFail();

    $this->actingAs($operator)
        ->get(route('notifications.open', $notification->id))
        ->assertRedirect(route('tasks.show', $task));

    expect($notification->fresh()->read_at)->not->toBeNull();
});

test('users can mark all of their notifications as read and clear read history', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $project = createNotificationTestProject($manager);

    $this->actingAs($manager);
    Task::factory()->count(2)->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $manager->id,
    ]);

    expect($operator->unreadNotifications()->count())->toBe(2);

    $this->actingAs($operator)
        ->patch(route('notifications.read-all'))
        ->assertRedirect();

    expect($operator->fresh()->unreadNotifications()->count())->toBe(0)
        ->and($operator->readNotifications()->count())->toBe(2);

    $this->actingAs($operator)
        ->delete(route('notifications.clear-read'))
        ->assertRedirect();

    expect($operator->notifications()->count())->toBe(0);
});

test('notification center filters notifications by category', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $project = createNotificationTestProject($manager);

    $this->actingAs($manager);
    Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $manager->id,
    ]);

    $operator->notify(new FlowNotification(
        kind: 'automation',
        titleKey: 'Automation category example',
        messageKey: 'Automation category example message',
        parameters: [],
        routeName: 'dashboard',
        routeParameters: [],
        icon: 'bi-lightning-charge',
    ));

    expect($operator->fresh()->notifications()->count())->toBe(2);

    $this->actingAs($operator)
        ->get(route('notifications.index', ['category' => 'assignments']))
        ->assertOk()
        ->assertViewHas('notifications', function ($notifications): bool {
            return $notifications->count() === 1
                && $notifications->every(
                    fn ($notification): bool => ($notification->data['category'] ?? null) === 'assignments'
                );
        })
        ->assertSee(__('Task assigned to you'));
});

test('users can disable in app notifications for a category', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $project = createNotificationTestProject($manager);

    $operator->preference()->create([
        'notification_preferences' => [
            'in_app' => [
                'assignments' => false,
            ],
            'mail' => [],
        ],
    ]);

    $this->actingAs($manager);
    Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $manager->id,
    ]);

    expect($operator->notifications()->count())->toBe(0);
});
