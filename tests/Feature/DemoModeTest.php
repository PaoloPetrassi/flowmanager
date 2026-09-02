<?php

use App\Models\Company;
use App\Models\User;
use Database\Seeders\DemoUserSeeder;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    config()->set('flowmanager.demo.enabled', true);
    config()->set('flowmanager.demo.read_only', true);
    config()->set('flowmanager.demo.email', 'demo@flowmanager.test');
    config()->set('flowmanager.demo.password', 'FlowManagerDemo!2026');
    config()->set('flowmanager.demo.role', 'administrator');

    $this->seed(RolePermissionSeeder::class);
    $this->seed(DemoUserSeeder::class);
});

function demoModeUser(): User
{
    return User::query()
        ->where('email', config('flowmanager.demo.email'))
        ->firstOrFail();
}

test('guest can enter the seeded demo account with one click', function () {
    $demo = demoModeUser();

    $this->post(route('demo.login'))
        ->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($demo);

    expect($demo->is_demo)->toBeTrue()
        ->and($demo->isDemoAccount())->toBeTrue();
});

test('demo account can browse administrator-only integration documentation', function () {
    $demo = demoModeUser();

    $this->actingAs($demo)
        ->get(route('integrations.api.docs'))
        ->assertOk()
        ->assertSee('OpenAPI 3.1')
        ->assertSee(__('Signed outbound webhooks'));
});

test('read-only demo account cannot mutate business data', function () {
    $demo = demoModeUser();
    $initialCount = Company::query()->count();

    $this->actingAs($demo)
        ->from(route('companies.index'))
        ->post(route('companies.store'), [
            'name' => 'Blocked Demo Company',
        ])
        ->assertRedirect(route('companies.index'))
        ->assertSessionHas('error');

    expect(Company::query()->count())->toBe($initialCount)
        ->and(Company::query()->where('name', 'Blocked Demo Company')->exists())->toBeFalse();
});


test('demo account bypasses mandatory administrator two factor setup', function () {
    config()->set('flowmanager.security.two_factor_required_for_administrators', true);
    $demo = demoModeUser();

    $this->actingAs($demo)
        ->get(route('dashboard'))
        ->assertOk();
});

test('demo account can still update interface preferences', function () {
    $demo = demoModeUser();

    $this->actingAs($demo)
        ->put(route('preferences.update'), [
            'theme' => 'dark',
            'density' => 'compact',
        ])
        ->assertRedirect();

    expect($demo->preference()->firstOrFail()->theme)->toBe('dark')
        ->and($demo->preference()->firstOrFail()->density)->toBe('compact');
});
