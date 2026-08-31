<?php

namespace App\Jobs;

use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Illuminate\Support\Facades\Http;

class DeliverWebhookJob extends TrackedJob
{
    public function __construct(
        string $trackingUuid,
        public int $webhookId,
        public string $event,
        public array $payload,
    ) {
        parent::__construct($trackingUuid);
    }

    public function handle(): void
    {
        $this->begin(__('Delivering webhook...'));

        $webhook = Webhook::query()->find($this->webhookId);

        if (! $webhook || ! $webhook->is_active) {
            $this->complete([], __('Webhook is no longer active.'));

            return;
        }

        $json = json_encode($this->payload, JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
        $response = Http::timeout(10)
            ->retry(2, 250)
            ->withHeaders([
                'X-FlowManager-Event' => $this->event,
                'X-FlowManager-Signature' => hash_hmac('sha256', $json, $webhook->secret ?? ''),
                'User-Agent' => 'FlowManager/'.config('flowmanager.version'),
            ])
            ->post($webhook->url, $this->payload);

        WebhookDelivery::create([
            'webhook_id' => $webhook->id,
            'event' => $this->event,
            'payload' => $this->payload,
            'status_code' => $response->status(),
            'successful' => $response->successful(),
            'response_body' => mb_substr($response->body(), 0, 4000),
            'delivered_at' => now(),
        ]);

        if (! $response->successful()) {
            throw new \RuntimeException(
                'Webhook returned HTTP '.$response->status().'.'
            );
        }

        $this->complete(
            ['status_code' => $response->status()],
            __('Webhook delivered successfully.'),
        );
    }
}
