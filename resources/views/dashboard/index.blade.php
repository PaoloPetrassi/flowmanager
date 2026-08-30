@extends('layouts.app')

@section('title', __('Dashboard'))
@section('page-title', __('Dashboard'))
@section('page-subtitle', __('Operational overview and work that needs your attention'))

@section('content')
    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
            <div class="fw-semibold">{{ __('Welcome back, :name.', ['name' => $currentUser->name]) }}</div>
            <div class="text-secondary small">
                @if ($overdueTaskCount > 0)
                    {{ trans_choice('ui.overdue_tasks', $overdueTaskCount, ['count' => $overdueTaskCount]) }}
                @else
                    {{ __('No overdue tasks are currently assigned to you.') }}
                @endif
            </div>
        </div>

        <div class="d-flex flex-wrap gap-2">
            @can('create', App\Models\Task::class)
                <a href="{{ route('tasks.create', ['assign_to_me' => 1]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-check2-square me-1"></i>
                    {{ __('New task') }}
                </a>
            @endcan

            @can('create', App\Models\Ticket::class)
                <a href="{{ route('tickets.create', ['assign_to_me' => 1]) }}" class="btn btn-outline-primary">
                    <i class="bi bi-ticket-perforated me-1"></i>
                    {{ __('New ticket') }}
                </a>
            @endcan

            @can('create', App\Models\Project::class)
                <a href="{{ route('projects.create', ['manage_by_me' => 1]) }}" class="btn btn-primary">
                    <i class="bi bi-plus-lg me-1"></i>
                    {{ __('New project') }}
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        @foreach ($stats as $stat)
            <div class="col-12 col-sm-6 col-xl-4 col-xxl-2">
                @if ($stat['url'])
                    <a href="{{ $stat['url'] }}" class="card fm-card fm-stat-card fm-stat-link h-100 text-reset">
                @else
                    <div class="card fm-card fm-stat-card h-100">
                @endif

                    <div class="card-body">
                        <div class="fm-stat-header">
                            <div class="fm-stat-icon">
                                <i class="bi {{ $stat['icon'] }}"></i>
                            </div>
                            <span class="fm-stat-label">{{ $stat['label'] }}</span>
                        </div>
                        <div class="fm-stat-value">{{ $stat['value'] }}</div>
                    </div>

                @if ($stat['url'])
                    </a>
                @else
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    <div class="row g-4 mb-4">
        @can('viewAny', App\Models\Task::class)
            <div class="col-12 col-xl-6">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title">{{ __('My open tasks') }}</h2>
                            <p class="fm-card-subtitle">{{ __('Nearest deadlines assigned to you') }}</p>
                        </div>
                        <a href="{{ route('tasks.index', ['assigned_to' => $currentUser->id]) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('View all') }}
                        </a>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse ($myTasks as $task)
                            @php
                                $isOverdue = $task->due_date && $task->due_date->isBefore(today());
                            @endphp

                            <div class="list-group-item fm-work-item">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a href="{{ route('tasks.show', $task) }}" class="fw-semibold text-dark">
                                            {{ $task->title }}
                                        </a>
                                        @if ($isOverdue)
                                            <span class="badge text-bg-danger">{{ __('Overdue') }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        {{ $task->project->code }} · {{ $task->project->company->name }}
                                        @if ($task->due_date)
                                            · {{ __('Due :date', ['date' => $task->due_date->format('d/m/Y')]) }}
                                        @endif
                                    </div>
                                </div>

                                @can('update', $task)
                                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="{{ __('Mark completed') }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @empty
                            <div class="p-4 text-center text-secondary">
                                {{ __('No open tasks are assigned to you.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endcan

        @can('viewAny', App\Models\Ticket::class)
            <div class="col-12 col-xl-6">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title">{{ __('My open tickets') }}</h2>
                            <p class="fm-card-subtitle">{{ __('Support requests currently assigned to you') }}</p>
                        </div>
                        <a href="{{ route('tickets.index', ['assigned_to' => $currentUser->id]) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('View all') }}
                        </a>
                    </div>

                    <div class="list-group list-group-flush">
                        @forelse ($myTickets as $ticket)
                            <div class="list-group-item fm-work-item">
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <a href="{{ route('tickets.show', $ticket) }}" class="fw-semibold text-dark">
                                            {{ $ticket->subject }}
                                        </a>
                                        @if ($ticket->priority->value === 'urgent')
                                            <span class="badge text-bg-danger">{{ __('Urgent') }}</span>
                                        @elseif ($ticket->priority->value === 'high')
                                            <span class="badge text-bg-warning">{{ __('High') }}</span>
                                        @endif
                                    </div>
                                    <div class="small text-secondary mt-1">
                                        {{ $ticket->reference }}
                                        @if ($ticket->company)
                                            · {{ $ticket->company->name }}
                                        @endif
                                        · {{ $ticket->status->label() }}
                                    </div>
                                </div>

                                @can('update', $ticket)
                                    <form method="POST" action="{{ route('tickets.resolve', $ticket) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="{{ __('Mark resolved') }}">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>
                                @endcan
                            </div>
                        @empty
                            <div class="p-4 text-center text-secondary">
                                {{ __('No open tickets are assigned to you.') }}
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        @endcan
    </div>

    <div class="row g-4">
        @can('viewAny', App\Models\Project::class)
            <div class="col-12 col-xl-7">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title">{{ __('Projects I manage') }}</h2>
                            <p class="fm-card-subtitle">{{ __('Active delivery under your responsibility') }}</p>
                        </div>
                        <a href="{{ route('projects.index', ['manager_id' => $currentUser->id]) }}" class="btn btn-sm btn-outline-secondary">
                            {{ __('View all') }}
                        </a>
                    </div>

                    <div class="table-responsive">
                        <table class="table align-middle mb-0 fm-table">
                            <thead>
                                <tr>
                                    <th>{{ __('Project') }}</th>
                                    <th>{{ __('Company') }}</th>
                                    <th>{{ __('Status') }}</th>
                                    <th>{{ __('Open tasks') }}</th>
                                    <th>{{ __('Due') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($managedProjects as $project)
                                    <tr>
                                        <td>
                                            <a href="{{ route('projects.show', $project) }}" class="fw-semibold text-dark">
                                                {{ $project->name }}
                                            </a>
                                            <div class="small text-secondary">{{ $project->code }}</div>
                                        </td>
                                        <td>{{ $project->company->name }}</td>
                                        <td>{{ $project->status->label() }}</td>
                                        <td>{{ $project->open_tasks_count }} / {{ $project->tasks_count }}</td>
                                        <td>{{ $project->due_date?->format('d/m/Y') ?: '—' }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-4 text-secondary">
                                            {{ __('No active projects are currently managed by you.') }}
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endcan

        <div class="col-12 col-xl-5">
            <div class="card fm-card mb-4">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3">{{ __('Your access') }}</h2>

                    <div class="fm-access-user">
                        <div class="fm-access-avatar">
                            {{ strtoupper(substr($currentUser->name, 0, 1)) }}
                        </div>
                        <div>
                            <div class="fw-semibold">{{ $currentUser->name }}</div>
                            <small class="text-secondary">{{ $currentUser->email }}</small>
                        </div>
                    </div>

                    <hr>

                    <div class="fm-access-row">
                        <span>{{ __('Roles') }}</span>
                        <strong>{{ $currentUser->roles->count() }}</strong>
                    </div>
                    <div class="fm-access-row">
                        <span>{{ __('Permissions') }}</span>
                        <strong>{{ $permissionCount }}</strong>
                    </div>

                    <div class="mt-3">
                        @foreach ($currentUser->roles as $role)
                            <span class="badge text-bg-primary me-1">{{ __($role->name) }}</span>
                        @endforeach
                    </div>
                </div>
            </div>

            @if ($latestUsers->isNotEmpty())
                <div class="card fm-card">
                    <div class="card-header fm-card-header">
                        <div>
                            <h2 class="fm-card-title">{{ __('Recent users') }}</h2>
                            <p class="fm-card-subtitle">{{ __('Latest application accounts') }}</p>
                        </div>
                        @can('viewAny', App\Models\User::class)
                            <a href="{{ route('users.index') }}" class="btn btn-sm btn-outline-secondary">{{ __('View all') }}</a>
                        @endcan
                    </div>

                    <div class="list-group list-group-flush">
                        @foreach ($latestUsers as $user)
                            <a href="{{ route('users.show', $user) }}" class="list-group-item list-group-item-action fm-user-list-item">
                                <div class="fm-small-avatar">{{ strtoupper(substr($user->name, 0, 1)) }}</div>
                                <div class="flex-grow-1">
                                    <div class="fw-semibold">{{ $user->name }}</div>
                                    <small class="text-secondary">{{ $user->roles->pluck('name')->map(fn ($name) => __($name))->implode(', ') ?: __('No role') }}</small>
                                </div>
                                <i class="bi bi-chevron-right text-secondary"></i>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
