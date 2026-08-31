@extends('layouts.app')

@section('title', __('System'))
@section('page-title', __('System health'))
@section('page-subtitle', __('Runtime status, queues, scheduler, storage and database backups'))

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div class="text-secondary small">
            {{ __('FlowManager :version', ['version' => $metrics['version']]) }}
        </div>

        <a href="{{ route('system.jobs.index') }}" class="btn btn-outline-primary btn-sm">
            <i class="bi bi-stack me-1"></i>{{ __('Background jobs') }}
            @if (($metrics['queue_pending'] ?? 0) > 0)
                <span class="badge text-bg-primary ms-1">{{ $metrics['queue_pending'] }}</span>
            @endif
        </a>
    </div>

    <div class="row g-4 mb-4">
        @foreach ($checks as $name => $check)
            <div class="col-12 col-sm-6 col-xl">
                <div class="card fm-card h-100">
                    <div class="card-body d-flex align-items-start gap-3">
                        <span class="fm-health-icon {{ $check['ok'] ? 'is-ok' : 'is-error' }}">
                            <i class="bi {{ $check['ok'] ? 'bi-check-lg' : 'bi-exclamation-triangle' }}"></i>
                        </span>
                        <div class="min-w-0">
                            <div class="text-secondary small text-uppercase">{{ __(ucfirst($name)) }}</div>
                            <div class="fw-bold">{{ $check['ok'] ? __('OK') : __('Attention') }}</div>
                            <div class="small text-secondary text-break">{{ $check['message'] }}</div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title">{{ __('Runtime') }}</h2>
                        <p class="fm-card-subtitle">{{ __('Current application environment') }}</p>
                    </div>
                </div>
                <div class="card-body">
                    @foreach ([
                        __('Version') => $metrics['version'],
                        __('PHP') => $metrics['php'],
                        __('Laravel') => $metrics['laravel'],
                        __('Environment') => $metrics['environment'],
                        __('Debug mode') => $metrics['debug'] ? __('ON') : __('OFF'),
                        __('Database latency') => $metrics['database_latency_ms'] !== null ? $metrics['database_latency_ms'].' ms' : '—',
                        __('Queue connection') => $metrics['queue_connection'],
                        __('Queued jobs') => $metrics['queue_pending'] ?? '—',
                        __('Tracked active jobs') => $metrics['tracked_jobs'] ?? '—',
                        __('Failed jobs') => $metrics['failed_jobs'] ?? '—',
                        __('Active sessions') => $metrics['sessions'] ?? '—',
                        __('XLSX support') => $metrics['zip_available'] ? __('Available') : __('ZIP extension missing'),
                    ] as $label => $value)
                        <div class="d-flex justify-content-between gap-3 py-2 border-bottom">
                            <span class="text-secondary">{{ $label }}</span>
                            <strong class="text-end">{{ $value }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card fm-card mb-4">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title">{{ __('Storage') }}</h2>
                        <p class="fm-card-subtitle">{{ __('Local disk capacity used by the application') }}</p>
                    </div>
                </div>
                <div class="card-body">
                    @if ($metrics['disk_total'] && $metrics['disk_free'] !== null)
                        @php
                            $diskUsed = max(0, $metrics['disk_total'] - $metrics['disk_free']);
                            $diskPercent = $metrics['disk_total'] > 0
                                ? min(100, round(($diskUsed / $metrics['disk_total']) * 100, 1))
                                : 0;
                        @endphp

                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ __('Used') }}</span>
                            <strong>{{ $diskPercent }}%</strong>
                        </div>
                        <div class="progress mb-3" role="progressbar" aria-valuenow="{{ $diskPercent }}" aria-valuemin="0" aria-valuemax="100">
                            <div class="progress-bar" style="width: {{ $diskPercent }}%"></div>
                        </div>
                        <div class="small text-secondary">
                            {{ \App\Support\FileSize::format($diskUsed) }} /
                            {{ \App\Support\FileSize::format($metrics['disk_total']) }}
                        </div>
                    @else
                        <div class="text-secondary">{{ __('Disk capacity is not available on this filesystem.') }}</div>
                    @endif
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title">{{ __('Recent authentication activity') }}</h2>
                        <p class="fm-card-subtitle">{{ __('Latest account access events') }}</p>
                    </div>
                </div>
                <div class="list-group list-group-flush">
                    @forelse ($loginActivities as $activity)
                        <div class="list-group-item py-3">
                            <div class="d-flex justify-content-between gap-2">
                                <strong>{{ $activity->user?->name ?: $activity->email }}</strong>
                                <span class="badge text-bg-light">{{ __($activity->event) }}</span>
                            </div>
                            <div class="small text-secondary">
                                {{ $activity->created_at->format('d/m/Y H:i') }} · {{ $activity->ip_address ?: '—' }}
                            </div>
                        </div>
                    @empty
                        <div class="p-4 text-secondary">{{ __('No activity recorded.') }}</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title">{{ __('Database backups') }}</h2>
                        <p class="fm-card-subtitle">{{ __('Portable snapshots of FlowManager data and private attachments') }}</p>
                    </div>

                    @if (auth()->user()->hasPermission('system.manage'))
                        <form method="POST" action="{{ route('system.backups.store') }}">
                            @csrf
                            <button class="btn btn-primary btn-sm" type="submit">
                                <i class="bi bi-database-add me-1"></i>{{ __('Queue backup') }}
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Backup') }}</th>
                                    <th>{{ __('Created') }}</th>
                                    <th>{{ __('Size') }}</th>
                                    <th>{{ __('Integrity') }}</th>
                                    <th class="text-end">{{ __('Actions') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($backups as $backup)
                                    <tr>
                                        <td class="fw-semibold">{{ $backup['name'] }}</td>
                                        <td>{{ \Illuminate\Support\Carbon::createFromTimestamp($backup['modified_at'])->format('d/m/Y H:i') }}</td>
                                        <td>{{ \App\Support\FileSize::format($backup['size']) }}</td>
                                        <td>
                                            <span class="badge {{ $backup['valid'] ? 'text-bg-success' : 'text-bg-danger' }}">
                                                {{ $backup['valid'] ? __('Valid') : __('Invalid') }}
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <div class="d-inline-flex gap-1">
                                                <a class="btn btn-sm btn-outline-primary" href="{{ route('system.backups.download', $backup['name']) }}">
                                                    <i class="bi bi-download"></i>
                                                </a>

                                                @if (auth()->user()->hasPermission('system.manage'))
                                                    <form method="POST" action="{{ route('system.backups.destroy', $backup['name']) }}">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button
                                                            class="btn btn-sm btn-outline-danger"
                                                            type="submit"
                                                            data-confirm="{{ __('Delete this backup?') }}"
                                                        >
                                                            <i class="bi bi-trash"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-5">
                                            {{ __('No backups have been created yet.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="card-footer bg-transparent small text-secondary">
                    {{ __('Web backups are queued. Restore remains CLI-only for safety: php artisan flowmanager:restore backups/file.json') }}
                </div>
            </div>
        </div>
    </div>
@endsection
