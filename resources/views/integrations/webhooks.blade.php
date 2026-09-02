@extends('layouts.app')

@section('title', __('Webhooks'))
@section('page-title', __('Webhooks'))
@section('page-subtitle', __('Send signed FlowManager events to external systems'))

@section('content')
    @php($demoReadOnly = auth()->user()->isDemoAccount() && config('flowmanager.demo.read_only'))

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary">
            {{ __('Deliveries are processed by the webhooks queue and recorded below for troubleshooting.') }}
        </div>

        <a href="{{ route('integrations.api.docs') }}" class="btn btn-outline-primary">
            <i class="bi bi-braces me-2"></i>{{ __('API & webhook documentation') }}
        </a>
    </div>

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card fm-card">
                <div class="card-body">
                    <form method="POST" action="{{ route('integrations.webhooks.store') }}">
                        @csrf

                        <h5>{{ __('New webhook') }}</h5>

                        <div class="mb-3">
                            <label class="form-label" for="webhook-name">{{ __('Name') }}</label>
                            <input
                                id="webhook-name"
                                name="name"
                                value="{{ old('name') }}"
                                required
                                class="form-control @error('name') is-invalid @enderror"
                            >
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label" for="webhook-url">URL</label>
                            <input
                                id="webhook-url"
                                name="url"
                                value="{{ old('url') }}"
                                required
                                type="url"
                                class="form-control @error('url') is-invalid @enderror"
                                placeholder="https://example.com/webhooks/flowmanager"
                            >
                            @error('url')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <label class="form-label">{{ __('Events') }}</label>
                        @foreach ($supportedEvents as $event)
                            <div class="form-check">
                                <input
                                    type="checkbox"
                                    name="events[]"
                                    value="{{ $event }}"
                                    class="form-check-input"
                                    id="wh-{{ $loop->index }}"
                                    {{ in_array($event, old('events', []), true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="wh-{{ $loop->index }}">{{ $event }}</label>
                            </div>
                        @endforeach
                        @error('events')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror

                        <div class="alert alert-warning small mt-3 mb-0">
                            {{ __('The signing secret is shown only once. Store it in the receiving system before leaving this page.') }}
                        </div>

                        <button class="btn btn-primary w-100 mt-3" {{ $demoReadOnly ? 'disabled' : '' }}>
                            {{ __('Create webhook') }}
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            <div class="d-grid gap-4">
                @forelse ($webhooks as $webhook)
                    <div class="card fm-card">
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
                                <div class="min-w-0">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h5 class="mb-0">{{ $webhook->name }}</h5>
                                        <span class="badge {{ $webhook->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                            {{ $webhook->is_active ? __('Active') : __('Paused') }}
                                        </span>
                                    </div>
                                    <div class="text-break"><code>{{ $webhook->url }}</code></div>
                                    <div class="small text-secondary mt-2">
                                        {{ trans_choice(':count delivery|:count deliveries', $webhook->deliveries_count, ['count' => $webhook->deliveries_count]) }}
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-2">
                                    <form method="POST" action="{{ route('integrations.webhooks.test', $webhook) }}">
                                        @csrf
                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            {{ $demoReadOnly || ! $webhook->is_active ? 'disabled' : '' }}
                                        >
                                            <i class="bi bi-send me-1"></i>{{ __('Test') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('integrations.webhooks.toggle', $webhook) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button class="btn btn-sm btn-outline-secondary" {{ $demoReadOnly ? 'disabled' : '' }}>
                                            <i class="bi {{ $webhook->is_active ? 'bi-pause-circle' : 'bi-play-circle' }} me-1"></i>
                                            {{ $webhook->is_active ? __('Pause') : __('Enable') }}
                                        </button>
                                    </form>

                                    <form method="POST" action="{{ route('integrations.webhooks.destroy', $webhook) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button
                                            class="btn btn-sm btn-outline-danger"
                                            aria-label="{{ __('Delete') }}"
                                            {{ $demoReadOnly ? 'disabled' : '' }}
                                        >
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="d-flex flex-wrap gap-2 mt-3">
                                @foreach ($webhook->events as $event)
                                    <code class="border rounded px-2 py-1">{{ $event }}</code>
                                @endforeach
                            </div>

                            @if ($webhook->deliveries->isNotEmpty())
                                <div class="table-responsive mt-4">
                                    <table class="table table-sm fm-table align-middle mb-0">
                                        <thead>
                                            <tr>
                                                <th>{{ __('Event') }}</th>
                                                <th>{{ __('Delivery ID') }}</th>
                                                <th>{{ __('Status') }}</th>
                                                <th>{{ __('Delivered') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($webhook->deliveries->take(5) as $delivery)
                                                <tr>
                                                    <td><code>{{ $delivery->event }}</code></td>
                                                    <td>
                                                        <code>{{ \Illuminate\Support\Str::limit($delivery->payload['delivery_id'] ?? '—', 18) }}</code>
                                                    </td>
                                                    <td>
                                                        <span class="badge {{ $delivery->successful ? 'text-bg-success' : 'text-bg-danger' }}">
                                                            {{ $delivery->status_code ?? '—' }}
                                                        </span>
                                                    </td>
                                                    <td>{{ $delivery->delivered_at?->diffForHumans() ?: '—' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            @else
                                <div class="small text-secondary mt-4">{{ __('No deliveries yet.') }}</div>
                            @endif
                        </div>
                    </div>
                @empty
                    <div class="card fm-card">
                        <div class="card-body text-center py-5 text-secondary">
                            <i class="bi bi-broadcast-pin fs-1 d-block mb-3"></i>
                            {{ __('No webhooks configured.') }}
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endsection
