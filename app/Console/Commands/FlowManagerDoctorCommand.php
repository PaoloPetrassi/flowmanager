<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class FlowManagerDoctorCommand extends Command
{
    protected $signature = 'flowmanager:doctor {--ci : Skip checks that require running local workers}';

    protected $description = 'Check FlowManager local/runtime readiness';

    public function handle(BackupService $backups): int
    {
        $checks = [];

        $this->add($checks, 'APP_KEY', filled(config('app.key')), config('app.key') ? 'configured' : 'missing', true);
        $this->add($checks, 'Environment', true, (string) config('app.env'));
        $this->add($checks, 'Debug', ! app()->environment('production') || ! config('app.debug'), config('app.debug') ? 'ON' : 'OFF', app()->environment('production'));

        foreach (['pdo', 'mbstring', 'openssl', 'fileinfo', 'json', 'dom'] as $extension) {
            $this->add($checks, 'PHP '.$extension, extension_loaded($extension), extension_loaded($extension) ? 'loaded' : 'missing', true);
        }

        $zipAvailable = class_exists(\ZipArchive::class);
        $simpleXmlAvailable = function_exists('simplexml_load_string');
        $this->add($checks, 'PHP zip', $zipAvailable, $zipAvailable ? 'loaded' : 'missing (XLSX import unavailable)', false);
        $this->add($checks, 'PHP SimpleXML', $simpleXmlAvailable, $simpleXmlAvailable ? 'loaded' : 'missing (XLSX import unavailable)', false);

        try {
            $started = microtime(true);
            DB::select('select 1');
            $latency = (microtime(true) - $started) * 1000;
            $this->add($checks, 'Database', true, number_format($latency, 1).' ms', true);
        } catch (Throwable $exception) {
            $this->add($checks, 'Database', false, $exception->getMessage(), true);
        }

        foreach ([
            storage_path('app'),
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ] as $path) {
            $this->add(
                $checks,
                'Writable '.str_replace(base_path().DIRECTORY_SEPARATOR, '', $path),
                is_dir($path) && is_writable($path),
                is_dir($path) ? (is_writable($path) ? 'writable' : 'not writable') : 'missing',
                true,
            );
        }

        $this->add($checks, 'Queue table', Schema::hasTable('jobs'), Schema::hasTable('jobs') ? 'available' : 'missing', true);
        $this->add($checks, 'Failed jobs table', Schema::hasTable('failed_jobs'), Schema::hasTable('failed_jobs') ? 'available' : 'missing', true);
        $this->add($checks, 'Background jobs table', Schema::hasTable('background_jobs'), Schema::hasTable('background_jobs') ? 'available' : 'run migrations', true);

        if (! $this->option('ci')) {
            $this->runtimeHeartbeatCheck($checks, 'Scheduler heartbeat', 'flowmanager.scheduler_heartbeat');
            $this->runtimeHeartbeatCheck($checks, 'Queue worker heartbeat', 'flowmanager.queue_heartbeat');
        }

        $latestBackup = collect($backups->list())->first();
        $this->add(
            $checks,
            'Backup',
            $latestBackup ? (bool) $latestBackup['valid'] : true,
            $latestBackup ? $latestBackup['name'] : 'none yet',
            false,
        );

        $rows = collect($checks)->map(fn (array $check) => [
            $check['ok'] ? '<fg=green>OK</>' : ($check['critical'] ? '<fg=red>FAIL</>' : '<fg=yellow>WARN</>'),
            $check['name'],
            $check['message'],
        ])->all();

        $this->table(['Status', 'Check', 'Details'], $rows);

        $criticalFailures = collect($checks)
            ->filter(fn (array $check) => $check['critical'] && ! $check['ok'])
            ->count();

        if ($criticalFailures > 0) {
            $this->error("{$criticalFailures} critical check(s) failed.");

            return self::FAILURE;
        }

        $this->info('FlowManager is ready.');

        return self::SUCCESS;
    }

    private function runtimeHeartbeatCheck(array &$checks, string $name, string $cacheKey): void
    {
        $value = Cache::get($cacheKey);
        $timestamp = $value ? Carbon::parse($value) : null;
        $ok = $timestamp?->gt(now()->subMinutes(5)) ?? false;

        $this->add(
            $checks,
            $name,
            $ok,
            $timestamp?->diffForHumans() ?? 'not detected',
            false,
        );
    }

    private function add(
        array &$checks,
        string $name,
        bool $ok,
        string $message,
        bool $critical = false,
    ): void {
        $checks[] = compact('name', 'ok', 'message', 'critical');
    }
}
