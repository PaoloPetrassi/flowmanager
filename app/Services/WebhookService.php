<?php

namespace App\Services;

use App\Jobs\DeliverWebhookJob;
use App\Models\BackgroundJob;
use App\Models\Webhook;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class WebhookService
{
    public const SUPPORTED_EVENTS = [
        'created',
        'updated',
        'deleted',
        'task.completed',
        'ticket.resolved',
    ];

    public static function dispatch(string $event, Model $model, array $data = []): void
    {
        if (app()->environment('testing')) {
            return;
        }

        $payload = self::payload(
            event: $event,
            resource: class_basename($model),
            id: $model->getKey(),
            data: $data,
        );

        Webhook::query()
            ->where('is_active', true)
            ->get()
            ->filter(fn (Webhook $webhook) => in_array($event, $webhook->events ?? [], true))
            ->each(fn (Webhook $webhook) => self::queue($webhook, $event, $payload, auth()->id()));
    }

    public static function dispatchTest(Webhook $webhook, ?int $userId = null): string
    {
        $payload = self::payload(
            event: 'test',
            resource: 'Webhook',
            id: $webhook->getKey(),
            data: [
                'message' => 'FlowManager webhook test delivery',
                'webhook' => $webhook->name,
                'version' => config('flowmanager.version'),
            ],
        );

        return self::queue($webhook, 'test', $payload, $userId);
    }

    private static function payload(string $event, string $resource, int|string $id, array $data): array
    {
        return [
            'event' => $event,
            'occurred_at' => now()->toIso8601String(),
            'resource' => $resource,
            'id' => $id,
            'data' => $data,
        ];
    }

    private static function queue(Webhook $webhook, string $event, array $payload, ?int $userId): string
    {
        $payload['delivery_id'] = (string) Str::uuid();

        $tracking = BackgroundJob::create([
            'uuid' => (string) Str::uuid(),
            'user_id' => $userId,
            'type' => 'webhook',
            'name' => __('Webhook :name — :event', [
                'name' => $webhook->name,
                'event' => $event,
            ]),
            'queue' => config('flowmanager.queues.webhooks', 'webhooks'),
            'payload' => [
                'webhook_id' => $webhook->id,
                'event' => $event,
                'delivery_id' => $payload['delivery_id'],
            ],
        ]);

        DeliverWebhookJob::dispatch(
            $tracking->uuid,
            $webhook->id,
            $event,
            $payload,
        )->onQueue($tracking->queue);

        return $payload['delivery_id'];
    }
}
