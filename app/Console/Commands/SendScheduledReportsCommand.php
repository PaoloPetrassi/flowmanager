<?php

namespace App\Console\Commands;

use App\Jobs\SendScheduledReportJob;
use App\Models\BackgroundJob;
use App\Models\ScheduledReport;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class SendScheduledReportsCommand extends Command
{
    protected $signature = 'flowmanager:scheduled-reports';

    protected $description = 'Queue due FlowManager scheduled reports';

    public function handle(): int
    {
        $queued = 0;

        ScheduledReport::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query
                ->whereNull('next_run_at')
                ->orWhere('next_run_at', '<=', now()))
            ->get()
            ->each(function (ScheduledReport $schedule) use (&$queued): void {
                $alreadyQueued = BackgroundJob::query()
                    ->where('type', 'scheduled_report')
                    ->whereIn('status', [
                        BackgroundJob::STATUS_PENDING,
                        BackgroundJob::STATUS_PROCESSING,
                    ])
                    ->where('payload->scheduled_report_id', $schedule->id)
                    ->exists();

                if ($alreadyQueued) {
                    return;
                }

                $tracking = BackgroundJob::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => $schedule->user_id,
                    'type' => 'scheduled_report',
                    'name' => __('Scheduled report: :name', ['name' => $schedule->name]),
                    'queue' => config('flowmanager.queues.reports', 'reports'),
                    'payload' => ['scheduled_report_id' => $schedule->id],
                ]);

                SendScheduledReportJob::dispatch(
                    $tracking->uuid,
                    $schedule->id,
                )->onQueue($tracking->queue);

                $queued++;
            });

        $this->info(__('Queued :count scheduled report(s).', ['count' => $queued]));

        return self::SUCCESS;
    }
}
