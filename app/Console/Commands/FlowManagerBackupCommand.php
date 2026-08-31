<?php

namespace App\Console\Commands;

use App\Services\BackupService;
use Illuminate\Console\Command;

class FlowManagerBackupCommand extends Command
{
    protected $signature = 'flowmanager:backup {--verify : Verify the backup after creation}';
    protected $description = 'Create a FlowManager database backup.';

    public function handle(BackupService $backups): int
    {
        $file = $backups->create();
        $this->info('Backup created: '.$file);

        if ($this->option('verify') && ! $backups->verify($file)) {
            $this->error('Backup verification failed.');
            return self::FAILURE;
        }

        if ($this->option('verify')) {
            $this->info('Backup verified successfully.');
        }

        return self::SUCCESS;
    }
}
