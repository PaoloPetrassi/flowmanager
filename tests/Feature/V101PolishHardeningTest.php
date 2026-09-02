<?php

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function v101User(string $role = 'administrator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $role)->firstOrFail());

    return $user;
}

test('application shell exposes keyboard and landmark accessibility hooks', function () {
    $administrator = v101User();

    $this->actingAs($administrator)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('href="#fm-main-content"', false)
        ->assertSee('id="fm-main-content"', false)
        ->assertSee('aria-label="'.__('Primary navigation').'"', false)
        ->assertSee('role="combobox"', false)
        ->assertSee('role="listbox"', false);
});

test('command palette hides create commands from read only users', function () {
    $viewer = v101User('viewer');

    $this->actingAs($viewer)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee(__('New project'))
        ->assertDontSee(__('New task'))
        ->assertDontSee(__('New ticket'));
});

test('error flash messages use an assertive live region', function () {
    $administrator = v101User();

    $this->actingAs($administrator)
        ->withSession(['error' => 'QA error message'])
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('role="alert" aria-live="assertive"', false)
        ->assertSee('QA error message');
});

test('demo reset is blocked in production environments', function () {
    $this->app->detectEnvironment(fn () => 'production');

    $this->artisan('flowmanager:demo-reset', [
        '--force' => true,
        '--admin-password' => 'LocalDemoPassword!123',
    ])->assertExitCode(1);
});

test('v101 keeps a semantic release version and cacheable home action', function () {
    expect((string) config('flowmanager.version'))
        ->toMatch('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/')
        ->and(Route::getRoutes()->getByName('home')?->getActionName())
        ->toBe(App\Http\Controllers\HomeController::class);
});
