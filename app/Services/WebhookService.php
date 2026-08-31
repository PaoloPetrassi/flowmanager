<?php

namespace App\Services;

use App\Jobs\DeliverWebhookJob;
use App\Models\BackgroundJob;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookService
{
    public static function dispatch(string $event, Model $model, array $data = []): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $payload = [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'resource' => class_basename($model),
            'id' => $model->getKey(),
            'data' => $data,
        ];

        Webhook::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Webhook $webhook) => in_array($event, $webhook->events ?? [], true))
            ->each(function (Webhook $webhook) use ($event, $payload): void {
                $tracking = BackgroundJob::create([
                    'uuid' => (string) Str::uuid(),
                    'user_id' => auth()->id(),
                    'type' => 'webhook',
                    'name' => __('Webhook :name — :event', [
                        'name' => $webhook->name,
                        'event' => $event,
                    ]),
                    'queue' => config('flowmanager.queues.webhooks', 'webhooks'),
                    'payload' => [
                        'webhook_id' => $webhook->id,
                        'event' => $event,
                    ],
                ]);

                DeliverWebhookJob::dispatch(
                    $tracking->uuid,
                    $webhook->id,
                    $event,
                    $payload,
                )->onQueue($tracking->queue);
            });
    }
}
