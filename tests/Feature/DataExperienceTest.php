<?php

use App\Enums\TaskStatus;
use App\Models\Company;
use App\Models\Project;
use App\Models\Role;
use App\Models\Task;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function v013DataUser(string $role = 'administrator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::where('slug', $role)->firstOrFail());

    return $user;
}

test('bulk actions update selected tasks', function () {
    $operator = v013DataUser('operator');
    $project = Project::factory()->create(['is_template' => false]);
    $tasks = Task::factory()->count(2)->create(['project_id' => $project->id, 'status' => TaskStatus::Todo]);

    $this->actingAs($operator)->post(route('bulk.update', 'tasks'), [
        'ids' => $tasks->pluck('id')->all(), 'action' => 'status', 'value' => TaskStatus::InProgress->value,
    ])->assertRedirect();

    expect(Task::whereIn('id', $tasks->pluck('id'))->where('status', TaskStatus::InProgress->value)->count())->toBe(2);
});

test('saved filters belong to the current user', function () {
    $viewer = v013DataUser('viewer');
    $this->actingAs($viewer)->post(route('saved-filters.store'), [
        'resource_type' => 'tasks', 'name' => 'My overdue work', 'filters' => ['status' => 'in_progress'],
    ])->assertRedirect();
    expect($viewer->savedFilters()->where('name', 'My overdue work')->exists())->toBeTrue();
});

test('document register supports approval', function () {
    Storage::fake('local');
    $admin = v013DataUser();
    $company = Company::factory()->create();
    Storage::disk('local')->put('flowmanager/attachments/test.pdf', 'pdf');
    $attachment = $company->attachments()->create([
        'user_id' => $admin->id, 'original_name' => 'test.pdf', 'disk' => 'local', 'path' => 'flowmanager/attachments/test.pdf', 'mime_type' => 'application/pdf', 'extension' => 'pdf', 'size' => 3, 'document_status' => 'active', 'version' => 1,
    ]);

    $this->actingAs($admin)->patch(route('documents.approve', $attachment))->assertRedirect();
    expect($attachment->fresh()->document_status)->toBe('approved')->and($attachment->fresh()->approved_by)->toBe($admin->id);
});

test('analytics and document registry are available to viewers', function () {
    $viewer = v013DataUser('viewer');
    $this->actingAs($viewer)->get(route('analytics.index'))->assertOk();
    $this->actingAs($viewer)->get(route('documents.index'))->assertOk();
});

test('calendar exports an ics feed', function () {
    $viewer = v013DataUser('viewer');
    Project::factory()->create(['is_template' => false, 'due_date' => now()->addWeek()->toDateString()]);
    $this->actingAs($viewer)->get(route('calendar.ics'))->assertOk()->assertHeader('content-type', 'text/calendar; charset=utf-8')->assertSee('BEGIN:VCALENDAR');
});

test('command palette searches accessible records', function () {
    $viewer = v013DataUser('viewer');
    Company::factory()->create(['name' => 'Northwind Strategic']);
    $this->actingAs($viewer)->getJson(route('command-palette', ['q' => 'Northwind']))->assertOk()->assertJsonFragment(['label' => 'Northwind Strategic']);
});
