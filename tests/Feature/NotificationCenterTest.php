<?php

use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
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

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

test('assigning a task to another user creates a database notification', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $company = Company::factory()->create(['created_by' => $manager->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $manager->id,
        'created_by' => $manager->id,
    ]);

    $this->actingAs($manager);

    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'created_by' => $manager->id,
    ]);

    $notification = $operator->notifications()->firstOrFail();

    expect($notification->data['title_key'])->toBe('Task assigned to you');
    expect($notification->data['route_name'])->toBe('tasks.show');
    expect($notification->data['route_parameters'])->toBe([$task->id]);
});

test('opening a notification marks it as read and redirects to its record', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $company = Company::factory()->create(['created_by' => $manager->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $manager->id,
        'created_by' => $manager->id,
    ]);

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

test('users can mark all of their notifications as read', function () {
    $manager = createNotificationTestUser('manager');
    $operator = createNotificationTestUser('operator');
    $company = Company::factory()->create(['created_by' => $manager->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $manager->id,
        'created_by' => $manager->id,
    ]);

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

    expect($operator->fresh()->unreadNotifications()->count())->toBe(0);
});
