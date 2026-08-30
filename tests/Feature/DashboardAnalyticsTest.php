<?php

use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
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

test('administrator dashboard includes analytics panels', function () {
    $administrator = User::factory()->create();
    $administrator->roles()->attach(
        Role::query()->where('slug', 'administrator')->firstOrFail()
    );

    Task::factory()->create([
        'status' => TaskStatus::Completed,
        'completed_at' => now(),
        'assigned_to' => $administrator->id,
        'created_by' => $administrator->id,
    ]);

    Ticket::factory()->create([
        'status' => TicketStatus::Resolved,
        'resolved_at' => now(),
        'assigned_to' => $administrator->id,
        'created_by' => $administrator->id,
    ]);

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee(__('Six-month throughput'))
        ->assertSee(__('Tasks by status'))
        ->assertSee(__('Open tickets by priority'));
});
