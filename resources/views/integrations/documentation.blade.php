@extends('layouts.app')

@section('title', __('API documentation'))
@section('page-title', __('API documentation'))
@section('page-subtitle', __('REST endpoints, bearer-token scopes and signed outbound webhooks'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <span class="badge text-bg-primary me-2">OpenAPI 3.1</span>
            <span class="text-secondary">FlowManager v{{ config('flowmanager.version') }}</span>
        </div>

        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('integrations.api.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-key me-2"></i>{{ __('API tokens') }}
            </a>
            <a href="{{ route('api.openapi') }}" class="btn btn-primary" target="_blank" rel="noopener">
                <i class="bi bi-filetype-json me-2"></i>{{ __('Open raw specification') }}
            </a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-7">
            <div class="card fm-card h-100">
                <div class="card-body">
                    <h2 class="h5">{{ __('Authentication') }}</h2>
                    <p class="text-secondary">
                        {{ __('Create a token from API tokens and send it as an HTTP Bearer token. The token scope and the owning user permissions are both enforced.') }}
                    </p>

                    <pre class="bg-body-tertiary border rounded p-3 mb-4"><code>Authorization: Bearer fm_your_token</code></pre>

                    <h2 class="h5">{{ __('Base URL') }}</h2>
                    <pre class="bg-body-tertiary border rounded p-3 mb-4"><code>{{ rtrim(config('app.url'), '/') }}/api/v1</code></pre>

                    <h2 class="h5">{{ __('Read endpoints') }}</h2>
                    <div class="table-responsive mb-4">
                        <table class="table fm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Method') }}</th>
                                    <th>{{ __('Endpoint') }}</th>
                                    <th>{{ __('Scope') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($resources as $resource)
                                    <tr>
                                        <td><span class="badge text-bg-success">GET</span></td>
                                        <td><code>/{{ $resource }}</code></td>
                                        <td><code>read</code></td>
                                    </tr>
                                    <tr>
                                        <td><span class="badge text-bg-success">GET</span></td>
                                        <td><code>/{{ $resource }}/{id}</code></td>
                                        <td><code>read</code></td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h2 class="h5">{{ __('Inbound ticket') }}</h2>
                    <p class="text-secondary">
                        {{ __('The inbound endpoint creates a support ticket and requires a token with the write scope plus the tickets.create permission.') }}
                    </p>
                    <pre class="bg-body-tertiary border rounded p-3 mb-0"><code>POST /tickets/inbound
Content-Type: application/json

{
  "subject": "Inbound request",
  "body": "Message body",
  "from_email": "sender@example.com",
  "priority": "medium"
}</code></pre>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-5">
            <div class="card fm-card mb-4">
                <div class="card-body">
                    <h2 class="h5">{{ __('ui.api_pagination') }}</h2>
                    <p class="text-secondary mb-2">
                        {{ __('Collection endpoints accept per_page from 1 to 100 and return Laravel pagination metadata.') }}
                    </p>
                    <code>?per_page=50</code>
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="h5">{{ __('Response codes') }}</h2>
                    <div class="d-grid gap-2 small">
                        <div><code>200</code> — {{ __('Request completed') }}</div>
                        <div><code>201</code> — {{ __('Ticket created') }}</div>
                        <div><code>401</code> — {{ __('Missing, invalid or expired token') }}</div>
                        <div><code>403</code> — {{ __('Missing token scope or user permission') }}</div>
                        <div><code>404</code> — {{ __('Resource not found') }}</div>
                        <div><code>422</code> — {{ __('Validation failed') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card fm-card mt-4">
        <div class="card-body">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1">{{ __('Signed outbound webhooks') }}</h2>
                    <p class="text-secondary mb-0">
                        {{ __('Webhook deliveries are queued and signed with the secret shown once when the webhook is created.') }}
                    </p>
                </div>

                <a href="{{ route('integrations.webhooks.index') }}" class="btn btn-outline-primary">
                    <i class="bi bi-broadcast-pin me-2"></i>{{ __('Manage webhooks') }}
                </a>
            </div>

            <div class="row g-4">
                <div class="col-lg-5">
                    <h3 class="h6">{{ __('Supported events') }}</h3>
                    <div class="d-flex flex-wrap gap-2">
                        @foreach ($webhookEvents as $event)
                            <code class="border rounded px-2 py-1">{{ $event }}</code>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-7">
                    <h3 class="h6">{{ __('Signature verification') }}</h3>
                    <p class="small text-secondary">
                        {{ __('Use the exact raw request body. Concatenate the timestamp, a dot and the raw body, then calculate HMAC-SHA256 with the webhook secret. Compare the result with X-FlowManager-Signature using a timing-safe comparison.') }}
                    </p>

                    <pre class="bg-body-tertiary border rounded p-3 mb-0"><code>$timestamp = $_SERVER['HTTP_X_FLOWMANAGER_TIMESTAMP'];
$rawBody = file_get_contents('php://input');
$expected = 'sha256='.hash_hmac('sha256', $timestamp.'.'.$rawBody, $secret);
$valid = hash_equals($expected, $_SERVER['HTTP_X_FLOWMANAGER_SIGNATURE']);</code></pre>
                </div>
            </div>

            <hr>

            <div class="small text-secondary">
                <strong>{{ __('Delivery headers') }}:</strong>
                <code>X-FlowManager-Event</code>,
                <code>X-FlowManager-Delivery</code>,
                <code>X-FlowManager-Timestamp</code>,
                <code>X-FlowManager-Signature</code>.
            </div>
        </div>
    </div>
@endsection
