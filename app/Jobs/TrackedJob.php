<?php

namespace App\Jobs;

use App\Models\BackgroundJob;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Throwable;

abstract class TrackedJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    public array $backoff = [10, 60, 300];

    public int $timeout = 300;

    public function __construct(public string $trackingUuid)
    {
    }

    protected function begin(?string $message = null): void
    {
        $tracking = $this->tracking();

        if (! $tracking) {
            return;
        }

        $tracking->forceFill([
            'status' => BackgroundJob::STATUS_PROCESSING,
            'progress' => max(1, $tracking->progress),
            'attempts' => max($tracking->attempts + 1, $this->attempts()),
            'message' => $message,
            'error' => null,
            'started_at' => $tracking->started_at ?? now(),
            'finished_at' => null,
        ])->save();
    }

    protected function progress(int $progress, ?string $message = null): void
    {
        $this->tracking()?->forceFill([
            'progress' => max(0, min(100, $progress)),
            'message' => $message,
        ])->save();
    }

    protected function complete(array $result = [], ?string $message = null): void
    {
        $this->tracking()?->forceFill([
            'status' => BackgroundJob::STATUS_COMPLETED,
            'progress' => 100,
            'result' => $result,
            'message' => $message,
            'error' => null,
            'finished_at' => now(),
        ])->save();
    }

    public function failed(?Throwable $exception): void
    {
        $this->tracking()?->forceFill([
            'status' => BackgroundJob::STATUS_FAILED,
            'error' => mb_substr($exception?->getMessage() ?? 'Unknown background job failure.', 0, 10000),
            'finished_at' => now(),
        ])->save();
    }

    protected function tracking(): ?BackgroundJob
    {
        return BackgroundJob::query()->where('uuid', $this->trackingUuid)->first();
    }

    private function attempts(): int
    {
        return $this->job?->attempts() ?? 1;
    }
}
