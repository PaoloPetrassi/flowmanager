<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Throwable;

class FlowManagerReleaseCheckCommand extends Command
{
    protected $signature = 'flowmanager:release-check
        {--ci : Skip runtime heartbeat checks}
        {--production : Enforce production-oriented environment settings}
        {--build : Require a compiled Vite production manifest}';

    protected $description = 'Run non-deploying release-readiness checks for FlowManager';

    public function handle(): int
    {
        $checks = [];
        $production = (bool) $this->option('production');

        $this->add(
            $checks,
            'Version',
            preg_match('/^\d+\.\d+\.\d+(?:[-+][0-9A-Za-z.-]+)?$/', (string) config('flowmanager.version')) === 1,
            (string) config('flowmanager.version'),
            true,
        );

        $this->add(
            $checks,
            'Application key',
            filled(config('app.key')),
            filled(config('app.key')) ? 'configured' : 'missing',
            true,
        );

        if ($production) {
            $this->add($checks, 'APP_ENV', app()->environment('production'), (string) config('app.env'), true);
            $this->add($checks, 'APP_DEBUG', ! config('app.debug'), config('app.debug') ? 'ON' : 'OFF', true);
            $this->add(
                $checks,
                'Queue connection',
                config('queue.default') !== 'sync',
                (string) config('queue.default'),
                true,
            );
            $this->add(
                $checks,
                'Session driver',
                config('session.driver') !== 'array',
                (string) config('session.driver'),
                true,
            );
            $this->add(
                $checks,
                'Cache store',
                config('cache.default') !== 'array',
                (string) config('cache.default'),
                true,
            );
        } else {
            $this->add($checks, 'Environment', true, (string) config('app.env'));
        }

        if ($this->option('build')) {
            $manifest = public_path('build/manifest.json');
            $this->add(
                $checks,
                'Frontend build',
                is_file($manifest),
                is_file($manifest) ? 'manifest available' : 'run npm run build',
                true,
            );
        }

        $doctorExit = Artisan::call('flowmanager:doctor', [
            '--ci' => (bool) $this->option('ci'),
        ]);

        $this->add(
            $checks,
            'FlowManager doctor',
            $doctorExit === self::SUCCESS,
            $doctorExit === self::SUCCESS ? 'passed' : 'failed',
            true,
        );

        $this->checkCacheability($checks);

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
            $this->error("{$criticalFailures} release-readiness check(s) failed.");

            return self::FAILURE;
        }

        $this->info('FlowManager release checks passed. No deployment was performed.');

        return self::SUCCESS;
    }

    private function checkCacheability(array &$checks): void
    {
        try {
            foreach ([
                'config:cache' => 'Configuration cache',
                'route:cache' => 'Route cache',
                'view:cache' => 'View cache',
            ] as $command => $label) {
                try {
                    $exitCode = Artisan::call($command);
                    $this->add(
                        $checks,
                        $label,
                        $exitCode === self::SUCCESS,
                        $exitCode === self::SUCCESS ? 'cacheable' : 'command failed',
                        true,
                    );
                } catch (Throwable $exception) {
                    $this->add($checks, $label, false, $exception->getMessage(), true);
                }
            }
        } finally {
            Artisan::call('optimize:clear');
        }
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
