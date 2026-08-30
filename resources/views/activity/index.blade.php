@extends('layouts.app')

@section('title', __('Activity log'))
@section('page-title', __('Activity log'))
@section('page-subtitle', __('Track who changed what across FlowManager'))

@section('content')
    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('activity.index') }}" class="row g-3 align-items-end">
                <div class="col-12 col-xl-3">
                    <label class="form-label">{{ __('Search') }}</label>
                    <input type="search" name="search" value="{{ $filters['search'] }}" class="form-control" placeholder="{{ __('Record, user or IP address') }}">
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label">{{ __('Event') }}</label>
                    <select name="event" class="form-select">
                        <option value="">{{ __('All events') }}</option>
                        @foreach ($events as $event)
                            <option value="{{ $event }}" @selected($filters['event'] === $event)>{{ __(str_replace('_', ' ', $event)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-2">
                    <label class="form-label">{{ __('Resource') }}</label>
                    <select name="resource" class="form-select">
                        <option value="">{{ __('All resources') }}</option>
                        @foreach ($resources as $resource)
                            <option value="{{ $resource }}" @selected($filters['resource'] === $resource)>{{ __(ucfirst($resource)) }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-12 col-md-6 col-xl-2">
                    <label class="form-label">{{ __('User') }}</label>
                    <select name="user_id" class="form-select">
                        <option value="">{{ __('All users') }}</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected((string) $filters['user_id'] === (string) $user->id)>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3 col-xl-1">
                    <label class="form-label">{{ __('From') }}</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] }}" class="form-control">
                </div>
                <div class="col-6 col-md-3 col-xl-1">
                    <label class="form-label">{{ __('To') }}</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] }}" class="form-control">
                </div>
                <div class="col-12 col-xl-1 d-grid">
                    <button type="submit" class="btn btn-primary"><i class="bi bi-funnel"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card fm-card">
        <div class="card-header fm-card-header">
            <div>
                <h2 class="fm-card-title">{{ __('Recorded activity') }}</h2>
                <p class="fm-card-subtitle">{{ trans_choice('ui.counts.audit_events', $logs->total(), ['count' => $logs->total()]) }}</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0 fm-table">
                <thead>
                    <tr>
                        <th>{{ __('When') }}</th>
                        <th>{{ __('User') }}</th>
                        <th>{{ __('Resource') }}</th>
                        <th>{{ __('Event') }}</th>
                        <th>{{ __('Changes') }}</th>
                        <th>{{ __('IP address') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($logs as $log)
                        <tr>
                            <td class="text-nowrap">
                                <div class="fw-semibold">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                <div class="small text-secondary">{{ $log->created_at->diffForHumans() }}</div>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $log->user?->name ?: __('System') }}</div>
                                <div class="small text-secondary">{{ $log->user?->email ?: '—' }}</div>
                            </td>
                            <td>
                                <div class="small text-secondary">{{ $log->resourceLabel() }}</div>
                                @if ($log->subjectUrl())
                                    <a href="{{ $log->subjectUrl() }}" class="fw-semibold">{{ $log->auditable_label }}</a>
                                @else
                                    <span class="fw-semibold">{{ $log->auditable_label }}</span>
                                @endif
                            </td>
                            <td><span class="badge text-bg-light border">{{ __(str_replace('_', ' ', $log->event)) }}</span></td>
                            <td>
                                @if ($log->old_values || $log->new_values)
                                    <details class="fm-audit-details">
                                        <summary>{{ __('View changes') }}</summary>
                                        <div class="mt-2">
                                            @foreach (collect(array_keys($log->old_values ?? []))->merge(array_keys($log->new_values ?? []))->unique() as $field)
                                                <div class="fm-audit-change">
                                                    <span>{{ $log->fieldLabel($field) }}</span>
                                                    <code>{{ is_array(data_get($log->old_values, $field)) ? json_encode(data_get($log->old_values, $field)) : (data_get($log->old_values, $field) ?? '—') }}</code>
                                                    <i class="bi bi-arrow-right"></i>
                                                    <code>{{ is_array(data_get($log->new_values, $field)) ? json_encode(data_get($log->new_values, $field)) : (data_get($log->new_values, $field) ?? '—') }}</code>
                                                </div>
                                            @endforeach
                                        </div>
                                    </details>
                                @else
                                    <span class="text-secondary">—</span>
                                @endif
                            </td>
                            <td class="text-nowrap text-secondary">{{ $log->ip_address ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center py-5 text-secondary">{{ __('No activity matches the selected filters.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
            <div class="card-footer bg-white">{{ $logs->links() }}</div>
        @endif
    </div>
@endsection
