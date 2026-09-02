<?php

namespace App\Http\Controllers;

use App\Models\Webhook;
use App\Services\WebhookService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class WebhookController extends Controller
{
    public function index(): View
    {
        $this->authorizeIntegrations();

        $webhooks = Webhook::query()
            ->with(['deliveries' => fn ($query) => $query->latest('id')->limit(5)])
            ->withCount('deliveries')
            ->latest()
            ->get();

        return view('integrations.webhooks', [
            'webhooks' => $webhooks,
            'supportedEvents' => WebhookService::SUPPORTED_EVENTS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeIntegrations();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'url' => ['required', 'url:http,https', 'max:500'],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['required', Rule::in(WebhookService::SUPPORTED_EVENTS)],
        ]);

        $secret = bin2hex(random_bytes(32));

        Webhook::query()->create($data + [
            'secret' => $secret,
            'created_by' => auth()->id(),
            'is_active' => true,
        ]);

        return back()->with(
            'status',
            __('Webhook created. Copy the signing secret now: :secret', ['secret' => $secret])
        );
    }

    public function toggle(Webhook $webhook): RedirectResponse
    {
        $this->authorizeIntegrations();

        $webhook->update([
            'is_active' => ! $webhook->is_active,
        ]);

        return back()->with(
            'status',
            $webhook->is_active
                ? __('Webhook enabled.')
                : __('Webhook paused.')
        );
    }

    public function test(Webhook $webhook): RedirectResponse
    {
        $this->authorizeIntegrations();

        if (! $webhook->is_active) {
            return back()->with('error', __('Enable the webhook before sending a test delivery.'));
        }

        $deliveryId = WebhookService::dispatchTest($webhook, auth()->id());

        return back()->with(
            'status',
            __('Webhook test queued. Delivery ID: :id', ['id' => $deliveryId])
        );
    }

    public function destroy(Webhook $webhook): RedirectResponse
    {
        $this->authorizeIntegrations();

        $webhook->delete();

        return back()->with('status', __('Webhook deleted.'));
    }

    private function authorizeIntegrations(): void
    {
        abort_unless(auth()->user()->hasPermission('integrations.manage'), 403);
    }
}
