<?php

namespace App\Jobs;

use App\Services\BackupService;

class CreateBackupJob extends TrackedJob
{
    public function handle(BackupService $backups): void
    {
        $this->begin(__('Preparing backup...'));
        $this->progress(20, __('Collecting application data...'));

        $file = $backups->create();

        $this->complete(
            ['file' => basename($file)],
            __('Backup created successfully.'),
        );
    }
}
