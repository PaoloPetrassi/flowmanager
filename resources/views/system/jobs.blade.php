@extends('layouts.app')

@section('title', __('Background jobs'))
@section('page-title', __('Background jobs'))
@section('page-subtitle', __('Queued imports, reports, webhooks and system operations'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="d-flex flex-wrap gap-2">
            @forelse ($queueCounts as $queue => $count)
                <span class="badge rounded-pill text-bg-light border px-3 py-2">
                    {{ $queue }}: {{ $count }}
                </span>
            @empty
                <span class="text-secondary small">{{ __('No queued jobs right now.') }}</span>
            @endforelse
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('system.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-activity me-1"></i>{{ __('System health') }}
            </a>

            @if (auth()->user()->hasPermission('system.manage'))
                <form method="POST" action="{{ route('system.jobs.clear-completed') }}">
                    @csrf
                    @method('DELETE')
                    <button
                        type="submit"
                        class="btn btn-outline-danger btn-sm"
                        data-confirm="{{ __('Clear completed background jobs older than 7 days?') }}"
                    >
                        <i class="bi bi-trash3 me-1"></i>{{ __('Clear completed') }}
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="card fm-card mb-4">
        <div class="card-header fm-card-header">
            <div>
                <h2 class="fm-card-title">{{ __('Tracked jobs') }}</h2>
                <p class="fm-card-subtitle">{{ __('Application-level progress and execution history') }}</p>
            </div>
        </div>

        <div class="card-body border-bottom">
            <form method="GET" class="row g-2 align-items-end">
                <div class="col-sm-5 col-lg-3">
                    <label class="form-label">{{ __('Status') }}</label>
                    <select name="status" class="form-select">
                        <option value="">{{ __('All statuses') }}</option>
                        @foreach (['pending', 'processing', 'completed', 'failed'] as $option)
                            <option value="{{ $option }}" @selected($status === $option)>{{ __(ucfirst($option)) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-5 col-lg-3">
                    <label class="form-label">{{ __('Type') }}</label>
                    <select name="type" class="form-select">
                        <option value="">{{ __('All types') }}</option>
                        @foreach ($types as $option)
                            <option value="{{ $option }}" @selected($type === $option)>{{ __(str_replace('_', ' ', ucfirst($option))) }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-sm-2">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Filter') }}</button>
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table fm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Queue') }}</th>
                        <th>{{ __('User') }}</th>
                        <th style="min-width: 180px">{{ __('Progress') }}</th>
                        <th>{{ __('Attempts') }}</th>
                        <th>{{ __('Started') }}</th>
                        <th>{{ __('Finished') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($jobs as $job)
                        @php
                            $statusClass = match ($job->status) {
                                'completed' => 'text-bg-success',
                                'failed' => 'text-bg-danger',
                                'processing' => 'text-bg-primary',
                                default => 'text-bg-secondary',
                            };
                        @endphp
                        <tr>
                            <td>
                                <div class="fw-semibold">{{ $job->name }}</div>
                                <div class="d-flex flex-wrap align-items-center gap-2 mt-1">
                                    <span class="badge {{ $statusClass }}">{{ __(ucfirst($job->status)) }}</span>
                                    <span class="text-secondary small">{{ $job->created_at->format('d/m/Y H:i:s') }}</span>
                                </div>
                                @if ($job->message)
                                    <div class="small text-secondary mt-1">{{ $job->message }}</div>
                                @endif
                                @if ($job->error)
                                    <div class="small text-danger mt-1 text-break">{{ $job->error }}</div>
                                @endif
                            </td>
                            <td><code>{{ $job->queue }}</code></td>
                            <td>{{ $job->user?->name ?? '—' }}</td>
                            <td>
                                <div class="progress" role="progressbar" aria-valuenow="{{ $job->progress }}" aria-valuemin="0" aria-valuemax="100">
                                    <div class="progress-bar" style="width: {{ $job->progress }}%">{{ $job->progress }}%</div>
                                </div>
                            </td>
                            <td>{{ $job->attempts }}</td>
                            <td>{{ $job->started_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                            <td>{{ $job->finished_at?->format('d/m/Y H:i:s') ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center text-secondary py-5">
                                {{ __('No background jobs found.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($jobs->hasPages())
            <div class="card-footer bg-transparent">{{ $jobs->links() }}</div>
        @endif
    </div>

    <div class="card fm-card">
        <div class="card-header fm-card-header">
            <div>
                <h2 class="fm-card-title">{{ __('Failed queue jobs') }}</h2>
                <p class="fm-card-subtitle">{{ __('Jobs that exhausted their retry attempts') }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table fm-table align-middle mb-0">
                <thead>
                    <tr>
                        <th>{{ __('Job') }}</th>
                        <th>{{ __('Queue') }}</th>
                        <th>{{ __('Error') }}</th>
                        <th>{{ __('Failed') }}</th>
                        <th class="text-end">{{ __('Actions') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($failedJobs as $failed)
                        <tr>
                            <td class="fw-semibold text-break">{{ $failed->name }}</td>
                            <td><code>{{ $failed->queue }}</code></td>
                            <td class="text-danger small text-break">{{ $failed->exception }}</td>
                            <td>{{ \Illuminate\Support\Carbon::parse($failed->failed_at)->format('d/m/Y H:i:s') }}</td>
                            <td class="text-end">
                                @if (auth()->user()->hasPermission('system.manage'))
                                    <div class="d-inline-flex gap-1">
                                        <form method="POST" action="{{ route('system.jobs.retry', $failed->uuid) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary" title="{{ __('Retry') }}">
                                                <i class="bi bi-arrow-clockwise"></i>
                                            </button>
                                        </form>

                                        <form method="POST" action="{{ route('system.jobs.forget', $failed->uuid) }}">
                                            @csrf
                                            @method('DELETE')
                                            <button
                                                type="submit"
                                                class="btn btn-sm btn-outline-danger"
                                                title="{{ __('Forget') }}"
                                                data-confirm="{{ __('Remove this failed job from the queue log?') }}"
                                            >
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-center text-secondary py-5">
                                {{ __('No failed queue jobs.') }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($failedJobs->hasPages())
            <div class="card-footer bg-transparent">{{ $failedJobs->links() }}</div>
        @endif
    </div>
@endsection
