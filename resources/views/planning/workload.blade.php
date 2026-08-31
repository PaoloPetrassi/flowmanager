@extends('layouts.app')

@section('title', __('Workload'))
@section('page-title', __('Team workload'))
@section('page-subtitle', __('Open work, estimated effort and tracked time by user'))

@section('content')
    <div class="row g-4 mb-4">
        <div class="col-12 col-md-4"><div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Users with assigned work') }}</div><div class="display-6 fw-bold">{{ $users->where('open_tasks_count', '>', 0)->count() }}</div></div></div></div>
        <div class="col-12 col-md-4"><div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Open tasks') }}</div><div class="display-6 fw-bold">{{ $users->sum('open_tasks_count') }}</div></div></div></div>
        <div class="col-12 col-md-4"><div class="card fm-card h-100"><div class="card-body"><div class="text-secondary small">{{ __('Tracked hours') }}</div><div class="display-6 fw-bold">{{ number_format($users->sum('tracked_minutes_sum') / 60, 1) }}</div></div></div></div>
    </div>

    <div class="card fm-card"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Workload by user') }}</h2><p class="fm-card-subtitle">{{ __('A quick capacity view for managers') }}</p></div></div><div class="card-body p-0"><div class="table-responsive"><table class="table align-middle mb-0">
        <thead><tr><th>{{ __('User') }}</th><th>{{ __('Open tasks') }}</th><th>{{ __('Active projects') }}</th><th>{{ __('Estimated hours') }}</th><th>{{ __('Tracked hours') }}</th><th style="min-width:220px">{{ __('Load indicator') }}</th></tr></thead>
        <tbody>
            @foreach ($users as $user)
                @php($load = min(100, ($user->open_tasks_count * 10) + (($user->estimated_minutes_sum ?? 0) / 60)))
                <tr><td><div class="fw-semibold">{{ $user->name }}</div><div class="small text-secondary">{{ $user->email }}</div></td><td>{{ $user->open_tasks_count }}</td><td>{{ $user->active_projects_count }}</td><td>{{ number_format(($user->estimated_minutes_sum ?? 0) / 60, 1) }}</td><td>{{ number_format(($user->tracked_minutes_sum ?? 0) / 60, 1) }}</td><td><div class="progress" style="height:8px"><div class="progress-bar" style="width: {{ $load }}%"></div></div><div class="small text-secondary mt-1">{{ number_format($load, 0) }}%</div></td></tr>
            @endforeach
        </tbody>
    </table></div></div></div>
@endsection
