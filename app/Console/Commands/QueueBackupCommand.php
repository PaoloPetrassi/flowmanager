<?php

namespace App\Console\Commands;

use App\Jobs\CreateBackupJob;
use App\Models\BackgroundJob;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class QueueBackupCommand extends Command
{
    protected $signature = 'flowmanager:queue-backup';

    protected $description = 'Queue a tracked FlowManager backup';

    public function handle(): int
    {
        $alreadyQueued = BackgroundJob::query()
            ->where('type', 'backup')
            ->whereIn('status', [
                BackgroundJob::STATUS_PENDING,
                BackgroundJob::STATUS_PROCESSING,
            ])
            ->exists();

        if ($alreadyQueued) {
            $this->warn('A FlowManager backup is already pending or processing.');

            return self::SUCCESS;
        }

        $tracking = BackgroundJob::create([
            'uuid' => (string) Str::uuid(),
            'type' => 'backup',
            'name' => __('Scheduled FlowManager backup'),
            'queue' => config('flowmanager.queues.system', 'system'),
        ]);

        CreateBackupJob::dispatch($tracking->uuid)->onQueue($tracking->queue);

        $this->info('Backup queued.');

        return self::SUCCESS;
    }
}
