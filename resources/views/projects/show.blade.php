@extends('layouts.app')
@section('title', $project->name)
@section('page-title', $project->name)
@section('page-subtitle'){{ $project->code }} · {{ $project->company->name }}@endsection
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-4">
        <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>{{ __('Projects') }}</a>
        <div class="d-flex flex-wrap gap-2">
            @can('create', App\Models\Task::class)<a href="{{ route('tasks.create', ['project' => $project->id]) }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1"></i>{{ __('New task') }}</a>@endcan
            @can('create', App\Models\Project::class)
                <form method="POST" action="{{ route('projects.duplicate', $project) }}">@csrf<button class="btn btn-outline-secondary" type="submit"><i class="bi bi-copy me-1"></i>{{ __('Duplicate') }}</button></form>
                <form method="POST" action="{{ route('projects.template', $project) }}">@csrf<button class="btn btn-outline-secondary" type="submit"><i class="bi bi-bookmark-plus me-1"></i>{{ __('Save as template') }}</button></form>
            @endcan
            @can('update', $project)<a href="{{ route('projects.edit', $project) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>{{ __('Edit project') }}</a>@endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Project details') }}</h2><p class="fm-card-subtitle">{{ __('Core delivery information') }}</p></div></div><div class="card-body">
                <div class="row g-4">
                    <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Status') }}</div><div class="fw-semibold">{{ $project->status->label() }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Priority') }}</div><div class="fw-semibold">{{ $project->priority->label() }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Start date') }}</div><div class="fw-semibold">{{ $project->start_date?->format('d/m/Y') ?: '—' }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Due date') }}</div><div class="fw-semibold">{{ $project->due_date?->format('d/m/Y') ?: '—' }}</div></div>
                    <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Company') }}</div><a class="fw-semibold" href="{{ route('companies.show', $project->company) }}">{{ $project->company->name }}</a></div>
                    <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Project manager') }}</div><div class="fw-semibold">{{ $project->manager?->name ?: __('Unassigned') }}</div></div>
                    <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Reference contact') }}</div><div class="fw-semibold">@if($project->contact)<a href="{{ route('contacts.show', $project->contact) }}">{{ $project->contact->full_name }}</a>@else—@endif</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Budget') }}</div><div class="fw-semibold">{{ $project->budget !== null ? '€ '.number_format((float) $project->budget, 2, ',', '.') : '—' }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Tracked time') }}</div><div class="fw-semibold">{{ number_format(($project->tracked_minutes_sum ?? 0) / 60, 1) }} h</div></div>
                    <div class="col-12"><div class="d-flex justify-content-between small mb-1"><span class="text-secondary">{{ __('Progress') }}</span><strong>{{ $project->progressPercentage() }}%</strong></div><div class="progress fm-project-progress"><div class="progress-bar" style="width: {{ $project->progressPercentage() }}%"></div></div></div>
                    <div class="col-12"><div class="small text-secondary mb-1">{{ __('Description') }}</div><div>{!! nl2br(e($project->description ?: '—')) !!}</div></div>
                    @if($project->notes)<div class="col-12"><div class="small text-secondary mb-1">{{ __('Internal notes') }}</div><div>{!! nl2br(e($project->notes)) !!}</div></div>@endif
                </div>
            </div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Delivery summary') }}</h2><p class="fm-card-subtitle">{{ __('Scope and effort at a glance') }}</p></div></div><div class="card-body">
                <div class="row g-3 text-center"><div class="col-4"><div class="fm-kpi-mini"><strong>{{ $project->tasks_count }}</strong><span>{{ __('Tasks') }}</span></div></div><div class="col-4"><div class="fm-kpi-mini"><strong>{{ $project->completed_tasks_count }}</strong><span>{{ __('Completed') }}</span></div></div><div class="col-4"><div class="fm-kpi-mini"><strong>{{ $project->milestones->count() }}</strong><span>{{ __('Milestones') }}</span></div></div></div>
                <hr>
                <div class="d-flex justify-content-between py-2"><span class="text-secondary">{{ __('Estimated effort') }}</span><strong>{{ $project->estimated_minutes ? number_format($project->estimated_minutes / 60, 1).' h' : '—' }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span class="text-secondary">{{ __('Created by') }}</span><strong>{{ $project->creator?->name ?: __('System') }}</strong></div>
                <div class="d-flex justify-content-between py-2"><span class="text-secondary">{{ __('Updated') }}</span><strong>{{ $project->updated_at->format('d/m/Y H:i') }}</strong></div>
            </div></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Project team') }}</h2><p class="fm-card-subtitle">{{ __('People collaborating on this project') }}</p></div><span class="badge text-bg-light">{{ $project->teamMembers->count() }}</span></div><div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">
                    @forelse($project->teamMembers as $member)<div class="fm-team-member"><span class="fm-user-avatar">{{ strtoupper(substr($member->name,0,1)) }}</span><div class="flex-grow-1"><div class="fw-semibold">{{ $member->name }}</div><div class="small text-secondary">{{ $member->pivot->role ?: __('Team member') }} · {{ $member->email }}</div></div>@can('update',$project)<form method="POST" action="{{ route('projects.team.destroy', [$project,$member]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" title="{{ __('Remove') }}"><i class="bi bi-x-lg"></i></button></form>@endcan</div>@empty<div class="text-secondary">{{ __('No team members yet.') }}</div>@endforelse
                </div>
                @can('update', $project)
                    @if ($availableTeamMembers->isNotEmpty())
                        <form method="POST" action="{{ route('projects.team.store', $project) }}" class="row g-2">
                            @csrf
                            <div class="col-12 col-md-5">
                                <select class="form-select" name="user_id" required>
                                    <option value="">{{ __('Select user') }}</option>
                                    @foreach ($availableTeamMembers as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-12 col-md-5">
                                <input class="form-control" name="role" maxlength="80" placeholder="{{ __('Role in project') }}">
                            </div>
                            <div class="col-12 col-md-2 d-grid">
                                <button class="btn btn-outline-primary">{{ __('Add') }}</button>
                            </div>
                        </form>
                    @endif
                @endcan
            </div></div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Milestones') }}</h2><p class="fm-card-subtitle">{{ __('Key delivery checkpoints') }}</p></div></div><div class="card-body">
                <div class="d-flex flex-column gap-2 mb-3">@forelse($project->milestones as $milestone)<div class="fm-milestone {{ $milestone->completed_at ? 'is-complete':'' }}"><div class="flex-grow-1"><div class="fw-semibold">{{ $milestone->name }}</div><div class="small text-secondary">{{ $milestone->due_date?->format('d/m/Y') ?: __('No due date') }} · {{ trans_choice('ui.counts.tasks',$milestone->tasks_count,['count'=>$milestone->tasks_count]) }}</div></div>@can('update',$project)<form method="POST" action="{{ route('projects.milestones.toggle',[$project,$milestone]) }}">@csrf @method('PATCH')<button class="btn btn-sm {{ $milestone->completed_at ? 'btn-success':'btn-outline-success' }}" title="{{ __('Toggle completion') }}"><i class="bi bi-check-lg"></i></button></form><form method="POST" action="{{ route('projects.milestones.destroy',[$project,$milestone]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger" data-confirm="{{ __('Delete this milestone?') }}"><i class="bi bi-trash"></i></button></form>@endcan</div>@empty<div class="text-secondary">{{ __('No milestones yet.') }}</div>@endforelse</div>
                @can('update',$project)<form method="POST" action="{{ route('projects.milestones.store',$project) }}" class="row g-2">@csrf<div class="col-12 col-md-7"><input class="form-control" name="name" required maxlength="180" placeholder="{{ __('Milestone name') }}"></div><div class="col-8 col-md-3"><input class="form-control" type="date" name="due_date"></div><div class="col-4 col-md-2 d-grid"><button class="btn btn-outline-primary">{{ __('Add') }}</button></div></form>@endcan
            </div></div>
        </div>
    </div>

    <div class="card fm-card mb-4"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Project tasks') }}</h2><p class="fm-card-subtitle">{{ __('Tasks, subtasks, owners and deadlines') }}</p></div>@can('create',App\Models\Task::class)<a class="btn btn-sm btn-outline-primary" href="{{ route('tasks.create',['project'=>$project->id]) }}">{{ __('New task') }}</a>@endcan</div><div class="list-group list-group-flush">
        @forelse($project->tasks as $task)<div class="list-group-item py-3"><div class="d-flex justify-content-between gap-3"><div class="min-w-0"><a class="fw-semibold text-dark" href="{{ route('tasks.show',$task) }}">{{ $task->title }}</a><div class="small text-secondary">{{ $task->assignee?->name ?: __('Unassigned') }} · {{ $task->due_date?->format('d/m/Y') ?: __('No due date') }}</div></div><span class="badge text-bg-light align-self-start">{{ $task->status->label() }}</span></div>@if($task->subtasks->isNotEmpty())<div class="fm-subtask-list mt-2">@foreach($task->subtasks as $subtask)<a href="{{ route('tasks.show',$subtask) }}"><i class="bi bi-arrow-return-right me-1"></i>{{ $subtask->title }} <span>{{ $subtask->status->label() }}</span></a>@endforeach</div>@endif</div>@empty<div class="p-4 text-secondary">{{ __('No tasks yet.') }}</div>@endforelse
    </div></div>

    @include('partials.collaboration-panel', ['collaborationTarget' => $project])
@endsection
