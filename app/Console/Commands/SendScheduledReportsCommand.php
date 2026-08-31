<?php

namespace App\Console\Commands;

use App\Models\ScheduledReport;
use App\Services\ReportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendScheduledReportsCommand extends Command
{
    protected $signature = 'flowmanager:scheduled-reports';
    protected $description = 'Send due FlowManager scheduled reports';

    public function handle(ReportService $reports): int
    {
        ScheduledReport::query()
            ->where('is_active', true)
            ->where(fn ($query) => $query->whereNull('next_run_at')->orWhere('next_run_at', '<=', now()))
            ->get()
            ->each(function (ScheduledReport $schedule) use ($reports): void {
                try {
                    $rows = $reports->rows($schedule->report_type);
                    $columns = $reports->columns($schedule->report_type);
                    $csv = $this->csv($columns, $rows->all());

                    Mail::raw(__('Your scheduled FlowManager report :name is attached.', ['name' => $schedule->name]), function ($message) use ($schedule, $csv): void {
                        $message->to($schedule->email)
                            ->subject('[FlowManager] '.$schedule->name)
                            ->attachData($csv, 'flowmanager-'.now()->format('Ymd-His').'.csv', ['mime' => 'text/csv']);
                    });

                    $schedule->update([
                        'last_sent_at' => now(),
                        'next_run_at' => $this->nextRun($schedule->frequency),
                        'last_error' => null,
                    ]);
                } catch (\Throwable $exception) {
                    $schedule->update([
                        'next_run_at' => now()->addHour(),
                        'last_error' => mb_substr($exception->getMessage(), 0, 2000),
                    ]);
                }
            });

        return self::SUCCESS;
    }

    private function csv(array $columns, array $rows): string
    {
        $stream = fopen('php://temp', 'w+');
        fputcsv($stream, array_values($columns));
        foreach ($rows as $row) {
            fputcsv($stream, array_map(fn ($key) => $row[$key] ?? null, array_keys($columns)));
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
