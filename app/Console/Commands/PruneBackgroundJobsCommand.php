<?php

namespace App\Console\Commands;

use App\Models\BackgroundJob;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneBackgroundJobsCommand extends Command
{
    protected $signature = 'flowmanager:prune-jobs';

    protected $description = 'Prune old FlowManager background-job history and staged import files';

    public function handle(): int
    {
        $historyDays = max(1, (int) config('flowmanager.jobs.history_days', 30));
        $cutoff = now()->subDays($historyDays);

        $deletedJobs = BackgroundJob::query()
            ->whereIn('status', [
                BackgroundJob::STATUS_COMPLETED,
                BackgroundJob::STATUS_FAILED,
            ])
            ->where('finished_at', '<', $cutoff)
            ->delete();

        $deletedFiles = 0;
        $tempCutoff = now()->subDay()->timestamp;

        foreach (Storage::disk('local')->files('imports/tmp') as $file) {
            if (Storage::disk('local')->lastModified($file) < $tempCutoff) {
                Storage::disk('local')->delete($file);
                $deletedFiles++;
            }
        }

        $this->info("Pruned {$deletedJobs} background-job records and {$deletedFiles} staged import files.");

        return self::SUCCESS;
    }
}
