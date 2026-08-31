<?php

namespace App\Http\Controllers;

use App\Jobs\CreateBackupJob;
use App\Models\BackgroundJob;
use App\Models\LoginActivity;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemController extends Controller
{
    public function index(Request $request, BackupService $backups): View
    {
        abort_unless($request->user()->hasPermission('system.view'), 403);

        [$databaseOk, $databaseMessage, $databaseLatency] = $this->databaseCheck();

        $storagePath = storage_path('app');
        $storageOk = is_dir($storagePath) && is_writable($storagePath);
        $schedulerHeartbeat = $this->heartbeat('flowmanager.scheduler_heartbeat');
        $queueHeartbeat = $this->heartbeat('flowmanager.queue_heartbeat');

        $queuePending = Schema::hasTable('jobs') ? DB::table('jobs')->count() : null;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null;
        $sessions = Schema::hasTable('sessions') ? DB::table('sessions')->count() : null;
        $trackedJobs = Schema::hasTable('background_jobs')
            ? BackgroundJob::query()
                ->whereIn('status', [
                    BackgroundJob::STATUS_PENDING,
                    BackgroundJob::STATUS_PROCESSING,
                ])
                ->count()
            : null;

        $diskTotal = @disk_total_space($storagePath);
        $diskFree = @disk_free_space($storagePath);
        $latestBackup = collect($backups->list())->first();

        return view('system.index', [
            'checks' => [
                'application' => [
                    'ok' => true,
                    'message' => config('app.env'),
                ],
                'database' => [
                    'ok' => $databaseOk,
                    'message' => $databaseMessage,
                ],
                'storage' => [
                    'ok' => $storageOk,
                    'message' => $storagePath,
                ],
                'scheduler' => [
                    'ok' => $schedulerHeartbeat['ok'],
                    'message' => $schedulerHeartbeat['message'],
                ],
                'queue' => [
                    'ok' => $queueHeartbeat['ok'],
                    'message' => $queueHeartbeat['message'],
                ],
            ],
            'metrics' => [
                'version' => config('flowmanager.version'),
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'environment' => config('app.env'),
                'debug' => config('app.debug'),
                'database_latency_ms' => $databaseLatency,
                'queue_connection' => config('queue.default'),
                'queue_pending' => $queuePending,
                'tracked_jobs' => $trackedJobs,
                'failed_jobs' => $failedJobs,
                'sessions' => $sessions,
                'disk_total' => is_numeric($diskTotal) ? (float) $diskTotal : null,
                'disk_free' => is_numeric($diskFree) ? (float) $diskFree : null,
                'last_backup' => $latestBackup,
                'zip_available' => class_exists(\ZipArchive::class),
            ],
            'backups' => $backups->list(),
            'loginActivities' => LoginActivity::query()
                ->with('user:id,name,email')
                ->latest('created_at')
                ->limit(25)
                ->get(),
        ]);
    }

    public function createBackup(Request $request): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('system.manage'), 403);

        $tracking = BackgroundJob::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $request->user()->id,
            'type' => 'backup',
            'name' => __('FlowManager backup'),
            'queue' => config('flowmanager.queues.system', 'system'),
        ]);

        CreateBackupJob::dispatch($tracking->uuid)->onQueue($tracking->queue);

        return redirect()
            ->route('system.jobs.index')
            ->with('status', __('Backup queued. It will continue in the background.'));
    }

    public function downloadBackup(
        Request $request,
        string $filename,
    ): StreamedResponse {
        abort_unless($request->user()->hasPermission('system.view'), 403);

        $path = 'backups/'.basename($filename);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function deleteBackup(
        Request $request,
        string $filename,
        BackupService $backups,
    ): RedirectResponse {
        abort_unless($request->user()->hasPermission('system.manage'), 403);

        $backups->delete($filename);

        return back()->with('status', __('Backup deleted.'));
    }

    private function databaseCheck(): array
    {
        try {
            $startedAt = microtime(true);
            DB::select('select 1');
            $latency = round((microtime(true) - $startedAt) * 1000, 1);

            return [true, __('Connected (:latency ms)', ['latency' => $latency]), $latency];
        } catch (\Throwable $exception) {
            return [false, $exception->getMessage(), null];
        }
    }

    private function heartbeat(string $key): array
    {
        $value = Cache::get($key);
        $at = $value ? Carbon::parse($value) : null;

        return [
            'ok' => $at?->gt(now()->subMinutes(5)) ?? false,
            'message' => $at?->diffForHumans() ?? __('Never'),
        ];
    }
}
