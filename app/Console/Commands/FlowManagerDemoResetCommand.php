<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class FlowManagerDemoResetCommand extends Command
{
    protected $signature = 'flowmanager:demo-reset
        {--force : Skip the confirmation prompt}
        {--admin-password= : Temporary local Administrator password used while seeding}';

    protected $description = 'Rebuild the local FlowManager database and repopulate the complete demo dataset';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('Demo reset is available only in local and testing environments.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This will permanently delete all local FlowManager data. Continue?')) {
            $this->info('Demo reset cancelled.');

            return self::SUCCESS;
        }

        $password = (string) ($this->option('admin-password') ?: config('flowmanager.admin.password'));

        if (blank($password)) {
            $this->error('Configure FLOWMANAGER_ADMIN_PASSWORD or pass --admin-password before resetting the demo database.');

            return self::FAILURE;
        }

        config()->set('flowmanager.admin.password', $password);

        $this->newLine();
        $this->warn('Rebuilding the local database...');

        $migrationExit = $this->call('migrate:fresh', [
            '--force' => true,
        ]);

        if ($migrationExit !== self::SUCCESS) {
            $this->error('Database rebuild failed.');

            return self::FAILURE;
        }

        $seedExit = $this->call('db:seed', [
            '--force' => true,
        ]);

        if ($seedExit !== self::SUCCESS) {
            $this->error('Demo seeding failed.');

            return self::FAILURE;
        }

        $this->newLine();
        $this->info('FlowManager demo data has been rebuilt successfully.');
        $this->line('Administrator: '.config('flowmanager.admin.email'));
        $this->line('Run <comment>php artisan flowmanager:doctor --ci</comment> to validate the rebuilt environment.');

        return self::SUCCESS;
    }
}
