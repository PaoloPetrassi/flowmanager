<?php

namespace App\Jobs;

use App\Models\ScheduledReport;
use App\Services\ReportService;
use Illuminate\Support\Facades\Mail;

class SendScheduledReportJob extends TrackedJob
{
    public function __construct(string $trackingUuid, public int $scheduledReportId)
    {
        parent::__construct($trackingUuid);
    }

    public function handle(ReportService $reports): void
    {
        $this->begin(__('Generating scheduled report...'));

        $schedule = ScheduledReport::query()->find($this->scheduledReportId);

        if (! $schedule || ! $schedule->is_active) {
            $this->complete([], __('Scheduled report is no longer active.'));

            return;
        }

        $rows = $reports->rows($schedule->report_type);
        $columns = $reports->columns($schedule->report_type);
        $csv = $this->csv($columns, $rows->all());

        $this->progress(65, __('Sending scheduled report...'));

        Mail::raw(
            __('Your scheduled FlowManager report :name is attached.', ['name' => $schedule->name]),
            function ($message) use ($schedule, $csv): void {
                $message->to($schedule->email)
                    ->subject('[FlowManager] '.$schedule->name)
                    ->attachData(
                        $csv,
                        'flowmanager-'.now()->format('Ymd-His').'.csv',
                        ['mime' => 'text/csv'],
                    );
            },
        );

        $schedule->update([
            'last_sent_at' => now(),
            'next_run_at' => $this->nextRun($schedule->frequency),
            'last_error' => null,
        ]);

        $this->complete(
            ['scheduled_report_id' => $schedule->id],
            __('Scheduled report sent.'),
        );
    }

    public function failed(?\Throwable $exception): void
    {
        parent::failed($exception);

        ScheduledReport::query()
            ->whereKey($this->scheduledReportId)
            ->update([
                'next_run_at' => now()->addHour(),
                'last_error' => mb_substr($exception?->getMessage() ?? 'Unknown error.', 0, 2000),
            ]);
    }

    private function csv(array $columns, array $rows): string
    {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, array_values($columns));

        foreach ($rows as $row) {
            fputcsv(
                $stream,
                array_map(fn ($key) => $row[$key] ?? null, array_keys($columns)),
            );
        }

        rewind($stream);
        $content = stream_get_contents($stream) ?: '';
        fclose($stream);

        return "\xEF\xBB\xBF".$content;
    }

    private function nextRun(string $frequency)
    {
        return match ($frequency) {
            'daily' => now()->addDay(),
            'monthly' => now()->addMonth(),
            default => now()->addWeek(),
        };
    }
}
