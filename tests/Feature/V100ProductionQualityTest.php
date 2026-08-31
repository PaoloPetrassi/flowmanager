<?php

use App\Jobs\CreateBackupJob;
use App\Jobs\ProcessImportJob;
use App\Jobs\SendScheduledReportJob;
use App\Models\BackgroundJob;
use App\Models\Company;
use App\Models\ImportRun;
use App\Models\Role;
use App\Models\ScheduledReport;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function v100User(string $role = 'administrator'): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', $role)->firstOrFail());

    return $user;
}

test('administrators can view the background jobs center', function () {
    $administrator = v100User();

    BackgroundJob::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $administrator->id,
        'type' => 'backup',
        'name' => 'Test backup',
        'queue' => 'system',
    ]);

    $this->actingAs($administrator)
        ->get(route('system.jobs.index'))
        ->assertOk()
        ->assertSee(__('Background jobs'))
        ->assertSee('Test backup');
});

test('non administrators cannot view the background jobs center', function () {
    $manager = v100User('manager');

    $this->actingAs($manager)
        ->get(route('system.jobs.index'))
        ->assertForbidden();
});

test('web backup requests are queued and tracked', function () {
    Queue::fake();
    $administrator = v100User();

    $this->actingAs($administrator)
        ->post(route('system.backups.store'))
        ->assertRedirect(route('system.jobs.index'));

    $tracking = BackgroundJob::query()->where('type', 'backup')->firstOrFail();

    expect($tracking->status)->toBe(BackgroundJob::STATUS_PENDING)
        ->and($tracking->queue)->toBe('system');

    Queue::assertPushedOn('system', CreateBackupJob::class);
});

test('tracked backup job records successful completion', function () {
    Storage::fake('local');
    $administrator = v100User();

    $tracking = BackgroundJob::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $administrator->id,
        'type' => 'backup',
        'name' => 'Tracked backup',
        'queue' => 'system',
    ]);

    CreateBackupJob::dispatchSync($tracking->uuid);

    $tracking->refresh();

    expect($tracking->status)->toBe(BackgroundJob::STATUS_COMPLETED)
        ->and($tracking->progress)->toBe(100)
        ->and($tracking->result['file'] ?? null)->not->toBeNull();

    Storage::disk('local')->assertExists('backups/'.$tracking->result['file']);
});

test('imports can be queued without blocking the request', function () {
    Queue::fake();
    Storage::fake('local');
    $administrator = v100User();

    Storage::disk('local')->put(
        'imports/tmp/demo.csv',
        "name,vat_number\nQueued Company,IT00000000001\n",
    );

    $this->actingAs($administrator)
        ->post(route('imports.store'), [
            'resource_type' => 'companies',
            'stored_path' => 'imports/tmp/demo.csv',
            'original_filename' => 'demo.csv',
            'mapping' => [
                'name' => 'name',
                'vat_number' => 'vat_number',
            ],
        ])
        ->assertRedirect(route('imports.index'));

    expect(BackgroundJob::query()->where('type', 'import')->exists())->toBeTrue();
    Queue::assertPushedOn('imports', ProcessImportJob::class);
});

test('background import preserves the initiating user', function () {
    Storage::fake('local');
    $administrator = v100User();

    Storage::disk('local')->put(
        'imports/tmp/direct.csv',
        "name,vat_number\nBackground Company,IT00000000002\n",
    );

    $tracking = BackgroundJob::create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $administrator->id,
        'type' => 'import',
        'name' => 'Direct import',
        'queue' => 'imports',
    ]);

    ProcessImportJob::dispatchSync(
        $tracking->uuid,
        $administrator->id,
        'companies',
        'imports/tmp/direct.csv',
        'direct.csv',
        [
            'name' => 'name',
            'vat_number' => 'vat_number',
        ],
    );

    expect(Company::query()->where('name', 'Background Company')->value('created_by'))
        ->toBe($administrator->id)
        ->and(ImportRun::query()->where('original_filename', 'direct.csv')->exists())
        ->toBeTrue()
        ->and($tracking->fresh()->status)
        ->toBe(BackgroundJob::STATUS_COMPLETED);
});

test('due scheduled reports are dispatched to the report queue', function () {
    Queue::fake();
    $administrator = v100User();

    ScheduledReport::create([
        'user_id' => $administrator->id,
        'name' => 'Weekly operations',
        'report_type' => 'projects',
        'frequency' => 'weekly',
        'email' => $administrator->email,
        'is_active' => true,
        'next_run_at' => now()->subMinute(),
    ]);

    $this->artisan('flowmanager:scheduled-reports')->assertExitCode(0);

    expect(BackgroundJob::query()->where('type', 'scheduled_report')->count())->toBe(1);
    Queue::assertPushedOn('reports', SendScheduledReportJob::class);
});

test('web responses include baseline security headers', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertHeader('X-Content-Type-Options', 'nosniff')
        ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
        ->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin')
        ->assertHeader('Permissions-Policy');
});

test('flowmanager doctor passes critical checks in ci mode', function () {
    $this->artisan('flowmanager:doctor', ['--ci' => true])
        ->assertExitCode(0);
});

test('scheduled backup command avoids duplicate queued backups', function () {
    Queue::fake();

    $this->artisan('flowmanager:queue-backup')->assertExitCode(0);
    $this->artisan('flowmanager:queue-backup')->assertExitCode(0);

    expect(BackgroundJob::query()->where('type', 'backup')->count())->toBe(1);
    Queue::assertPushedOn('system', CreateBackupJob::class);
});
