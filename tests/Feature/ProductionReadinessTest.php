<?php

use App\Models\Company;
use App\Models\Role;
use App\Models\User;
use App\Services\BackupService;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function productionReadinessUser(string $roleSlug): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $roleSlug)->firstOrFail());

    return $user;
}

test('only administrators can access system health', function () {
    $manager = productionReadinessUser('manager');
    $administrator = productionReadinessUser('administrator');

    $this->actingAs($manager)->get(route('system.index'))->assertForbidden();
    $this->actingAs($administrator)->get(route('system.index'))->assertOk();
});

test('system health reports a recent scheduler heartbeat', function () {
    $administrator = productionReadinessUser('administrator');
    Cache::put('flowmanager.scheduler_heartbeat', now()->toIso8601String(), now()->addMinutes(10));

    $this->actingAs($administrator)
        ->get(route('system.index'))
        ->assertOk()
        ->assertSee(__('System health'));
});

test('backup captures database data and private attachment files', function () {
    Storage::fake('local');
    $user = productionReadinessUser('administrator');
    $company = Company::factory()->create(['name' => 'Backup Company', 'created_by' => $user->id]);
    Storage::disk('local')->put('flowmanager/attachments/example.txt', 'private-file');

    $service = app(BackupService::class);
    $manifest = $service->create();

    expect($service->verify($manifest))->toBeTrue();
    Storage::disk('local')->assertExists($manifest);

    $payload = json_decode(Storage::disk('local')->get($manifest), true, flags: JSON_THROW_ON_ERROR);
    expect($payload['files']['count'])->toBe(1)
        ->and(collect($payload['tables']['companies'])->contains(fn ($row) => $row['id'] === $company->id))->toBeTrue();

    Storage::disk('local')->assertExists($payload['files']['snapshot_path'].'/flowmanager/attachments/example.txt');
});

test('backup restore recovers database values and private files', function () {
    Storage::fake('local');
    $user = productionReadinessUser('administrator');
    $company = Company::factory()->create(['name' => 'Original Company', 'created_by' => $user->id]);
    Storage::disk('local')->put('flowmanager/attachments/original.txt', 'original');

    $service = app(BackupService::class);
    $manifest = $service->create();

    $company->update(['name' => 'Changed Company']);
    Storage::disk('local')->put('flowmanager/attachments/original.txt', 'changed');
    Storage::disk('local')->put('flowmanager/attachments/new.txt', 'new');

    $service->restore($manifest);

    expect(Company::query()->findOrFail($company->id)->name)->toBe('Original Company')
        ->and(Storage::disk('local')->get('flowmanager/attachments/original.txt'))->toBe('original')
        ->and(Storage::disk('local')->exists('flowmanager/attachments/new.txt'))->toBeFalse();
});

test('backup retention removes manifests older than configured limit', function () {
    Storage::fake('local');
    productionReadinessUser('administrator');
    config()->set('flowmanager.backups.keep', 2);

    $service = app(BackupService::class);
    $service->create();
    usleep(1000);
    $service->create();
    usleep(1000);
    $service->create();

    expect($service->list())->toHaveCount(2);
});
