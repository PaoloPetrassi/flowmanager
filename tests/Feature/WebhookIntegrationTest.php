<?php

use App\Jobs\DeliverWebhookJob;
use App\Models\BackgroundJob;
use App\Models\Role;
use App\Models\User;
use App\Models\Webhook;
use App\Models\WebhookDelivery;
use Database\Seeders\RolePermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->seed(RolePermissionSeeder::class);
});

function webhookAdmin(): User
{
    $user = User::factory()->create();
    $user->roles()->attach(Role::query()->where('slug', 'administrator')->firstOrFail());

    return $user;
}

function testWebhook(User $user, array $overrides = []): Webhook
{
    return Webhook::query()->create(array_merge([
        'name' => 'CRM Receiver',
        'url' => 'https://example.test/webhooks/flowmanager',
        'secret' => str_repeat('a', 64),
        'events' => ['created', 'ticket.resolved'],
        'is_active' => true,
        'created_by' => $user->id,
    ], $overrides));
}

test('administrator can pause and enable a webhook', function () {
    $administrator = webhookAdmin();
    $webhook = testWebhook($administrator);

    $this->actingAs($administrator)
        ->patch(route('integrations.webhooks.toggle', $webhook))
        ->assertRedirect();

    expect($webhook->fresh()->is_active)->toBeFalse();

    $this->actingAs($administrator)
        ->patch(route('integrations.webhooks.toggle', $webhook))
        ->assertRedirect();

    expect($webhook->fresh()->is_active)->toBeTrue();
});

test('administrator can queue a test webhook delivery', function () {
    Queue::fake();
    $administrator = webhookAdmin();
    $webhook = testWebhook($administrator);

    $this->actingAs($administrator)
        ->post(route('integrations.webhooks.test', $webhook))
        ->assertRedirect()
        ->assertSessionHas('status');

    $tracking = BackgroundJob::query()->where('type', 'webhook')->firstOrFail();

    expect($tracking->payload['event'] ?? null)->toBe('test')
        ->and($tracking->payload['delivery_id'] ?? null)->not->toBeNull();

    Queue::assertPushedOn('webhooks', DeliverWebhookJob::class);
});

test('webhook delivery signs the exact json body with timestamp and secret', function () {
    $administrator = webhookAdmin();
    $webhook = testWebhook($administrator);
    $tracking = BackgroundJob::query()->create([
        'uuid' => (string) Str::uuid(),
        'user_id' => $administrator->id,
        'type' => 'webhook',
        'name' => 'Signature test',
        'queue' => 'webhooks',
    ]);
    $payload = [
        'delivery_id' => (string) Str::uuid(),
        'event' => 'created',
        'occurred_at' => now()->toIso8601String(),
        'resource' => 'Company',
        'id' => 42,
        'data' => ['name' => 'Signed Company'],
    ];

    Http::fake([
        '*' => Http::response(['accepted' => true], 200),
    ]);

    DeliverWebhookJob::dispatchSync(
        $tracking->uuid,
        $webhook->id,
        'created',
        $payload,
    );

    Http::assertSent(function (HttpRequest $request) use ($webhook, $payload): bool {
        $timestamp = $request->header('X-FlowManager-Timestamp')[0] ?? '';
        $signature = $request->header('X-FlowManager-Signature')[0] ?? '';
        $deliveryId = $request->header('X-FlowManager-Delivery')[0] ?? '';
        $expected = 'sha256='.hash_hmac(
            'sha256',
            $timestamp.'.'.$request->body(),
            $webhook->secret
        );

        return $request->url() === $webhook->url
            && $deliveryId === $payload['delivery_id']
            && hash_equals($expected, $signature);
    });

    $delivery = WebhookDelivery::query()->firstOrFail();

    expect($delivery->successful)->toBeTrue()
        ->and($delivery->status_code)->toBe(200)
        ->and($delivery->payload['delivery_id'])->toBe($payload['delivery_id']);
});
