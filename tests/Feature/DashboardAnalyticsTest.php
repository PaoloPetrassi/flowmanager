<?php

use App\Enums\TaskStatus;
use App\Enums\TicketStatus;
use App\Models\Company;
use App\Models\Role;
use App\Models\Task;
use App\Models\Ticket;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function dashboardAnalyticsAdministrator(): User
{
    $administrator = User::factory()->create();
    $administrator->roles()->attach(
        Role::query()->where('slug', 'administrator')->firstOrFail()
    );

    return $administrator;
}

test('administrator dashboard includes analytics panels', function () {
    $administrator = dashboardAnalyticsAdministrator();

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
        ->assertSee(__('Period throughput'))
        ->assertSee(__('Tasks by status'))
        ->assertSee(__('Open tickets by priority'))
        ->assertSee(__('Activity timeline'));
});

test('dashboard analytics supports a custom date range', function () {
    Carbon::setTestNow('2026-09-02 12:00:00');
    $administrator = dashboardAnalyticsAdministrator();

    Task::factory()->create([
        'status' => TaskStatus::Completed,
        'completed_at' => '2026-08-15 10:00:00',
        'assigned_to' => $administrator->id,
        'created_by' => $administrator->id,
    ]);

    Task::factory()->create([
        'status' => TaskStatus::Completed,
        'completed_at' => '2026-09-01 10:00:00',
        'assigned_to' => $administrator->id,
        'created_by' => $administrator->id,
    ]);

    $response = $this->actingAs($administrator)->get(route('dashboard', [
        'period' => 'custom',
        'date_from' => '2026-08-01',
        'date_to' => '2026-08-31',
    ]));

    $response->assertOk();

    $period = $response->viewData('period');
    $completedTasks = collect($response->viewData('periodKpis'))->firstWhere('key', 'completed_tasks');

    expect($period['key'])->toBe('custom')
        ->and($period['date_from'])->toBe('2026-08-01')
        ->and($period['date_to'])->toBe('2026-08-31')
        ->and($completedTasks['value'])->toBe(1);

    Carbon::setTestNow();
});

test('dashboard activity timeline includes tracked changes in the selected period', function () {
    $administrator = dashboardAnalyticsAdministrator();
    $this->actingAs($administrator);

    Company::factory()->create([
        'name' => 'Timeline Example Company',
        'created_by' => $administrator->id,
    ]);

    $this->get(route('dashboard', ['period' => '7d']))
        ->assertOk()
        ->assertSee('Timeline Example Company')
        ->assertSee(__('Activity timeline'));
});
