<?php

use App\Enums\ProjectStatus;
use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Company;
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

test('dashboard shows work assigned to the authenticated operator', function () {
    $operator = User::factory()->create();
    $operator->roles()->attach(
        Role::query()->where('slug', 'operator')->firstOrFail()
    );

    $company = Company::factory()->create(['created_by' => $operator->id]);
    $project = Project::factory()->create([
        'company_id' => $company->id,
        'manager_id' => $operator->id,
        'status' => ProjectStatus::Active,
        'created_by' => $operator->id,
    ]);
    $task = Task::factory()->create([
        'project_id' => $project->id,
        'assigned_to' => $operator->id,
        'status' => TaskStatus::InProgress,
        'created_by' => $operator->id,
    ]);
    $ticket = Ticket::factory()->create([
        'company_id' => $company->id,
        'assigned_to' => $operator->id,
        'status' => TicketStatus::Open,
        'created_by' => $operator->id,
    ]);

    $this->actingAs($operator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee($project->name)
        ->assertSee($task->title)
        ->assertSee($ticket->subject);
});
