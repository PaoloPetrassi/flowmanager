<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class FlowManagerRestoreCommand extends Command
{
    protected $signature = 'flowmanager:restore {file} {--force : Skip confirmation}';

    protected $description = 'Restore the application database from a FlowManager backup.';

    public function handle(BackupService $backups): int
    {
        $file = (string) $this->argument('file');

        if (! $backups->verify($file)) {
            $this->error('Backup not found or invalid.');

            return self::FAILURE;
        }

        if (! $this->option('force') && ! $this->confirm('This replaces current application data. Continue?')) {
            return self::SUCCESS;
        }

        $backups->restore($file);
        $this->info('Backup restored successfully.');

        return self::SUCCESS;
    }
}
