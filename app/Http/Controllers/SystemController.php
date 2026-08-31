<?php

namespace App\Http\Controllers;

use App\Models\LoginActivity;
use App\Services\BackupService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SystemController extends Controller
{
    public function index(Request $request, BackupService $backups): View
    {
        abort_unless($request->user()->hasPermission('system.view'), 403);

        $databaseOk = true;
        $databaseMessage = __('Connected');

        try {
            DB::select('select 1');
        } catch (\Throwable $exception) {
            $databaseOk = false;
            $databaseMessage = $exception->getMessage();
        }

        $storagePath = storage_path('app');
        $storageOk = is_dir($storagePath) && is_writable($storagePath);
        $heartbeat = Cache::get('flowmanager.scheduler_heartbeat');
        $heartbeatAt = $heartbeat ? Carbon::parse($heartbeat) : null;

        $queuePending = Schema::hasTable('jobs') ? DB::table('jobs')->count() : null;
        $failedJobs = Schema::hasTable('failed_jobs') ? DB::table('failed_jobs')->count() : null;
        $sessions = Schema::hasTable('sessions') ? DB::table('sessions')->count() : null;

        return view('system.index', [
            'checks' => [
                'application' => ['ok' => true, 'message' => config('app.env')],
                'database' => ['ok' => $databaseOk, 'message' => $databaseMessage],
                'storage' => ['ok' => $storageOk, 'message' => $storagePath],
                'scheduler' => [
                    'ok' => $heartbeatAt?->gt(now()->subMinutes(5)) ?? false,
                    'message' => $heartbeatAt?->diffForHumans() ?? __('Never'),
                ],
            ],
            'metrics' => [
                'php' => PHP_VERSION,
                'laravel' => app()->version(),
                'environment' => config('app.env'),
                'debug' => config('app.debug'),
                'queue_pending' => $queuePending,
                'failed_jobs' => $failedJobs,
                'sessions' => $sessions,
            ],
            'backups' => $backups->list(),
            'loginActivities' => LoginActivity::query()
                ->with('user:id,name,email')
                ->latest('created_at')
                ->limit(25)
                ->get(),
        ]);
    }

    public function createBackup(Request $request, BackupService $backups): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('system.manage'), 403);

        $file = $backups->create();

        return back()->with('status', __('Backup created: :file', ['file' => basename($file)]));
    }

    public function downloadBackup(Request $request, string $filename): StreamedResponse
    {
        abort_unless($request->user()->hasPermission('system.view'), 403);

        $path = 'backups/'.basename($filename);
        abort_unless(Storage::disk('local')->exists($path), 404);

        return Storage::disk('local')->download($path);
    }

    public function deleteBackup(Request $request, string $filename): RedirectResponse
    {
        abort_unless($request->user()->hasPermission('system.manage'), 403);

        $backups->delete($filename);

        return back()->with('status', __('Backup deleted.'));
    }
}
