@extends('layouts.app')
@section('title', $task->title)
@section('page-title', $task->title)
@section('page-subtitle'){{ $task->project->code }} — {{ $task->project->name }}@endsection
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary"><i class="bi bi-arrow-left me-1"></i>{{ __('Tasks') }}</a>
        <div class="d-flex flex-wrap gap-2">
            @can('create',App\Models\Task::class)<a href="{{ route('tasks.create',['parent'=>$task->id]) }}" class="btn btn-outline-secondary"><i class="bi bi-diagram-2 me-1"></i>{{ __('Add subtask') }}</a>@endcan
            @can('update',$task)
                @if($task->status->value==='completed')<form method="POST" action="{{ route('tasks.reopen',$task) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-primary"><i class="bi bi-arrow-counterclockwise me-1"></i>{{ __('Reopen') }}</button></form>
                @elseif($task->status->value!=='cancelled')<form method="POST" action="{{ route('tasks.complete',$task) }}">@csrf @method('PATCH')<button type="submit" class="btn btn-outline-success"><i class="bi bi-check-lg me-1"></i>{{ __('Mark completed') }}</button></form>@endif
                <a href="{{ route('tasks.edit',$task) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i>{{ __('Edit task') }}</a>
            @endcan
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Task details') }}</h2><p class="fm-card-subtitle">{{ __('Assignment, dependencies and delivery information') }}</p></div></div><div class="card-body"><div class="row g-4">
                <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Status') }}</div><div class="fw-semibold">{{ $task->status->label() }}</div></div>
                <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Priority') }}</div><div class="fw-semibold">{{ $task->priority->label() }}</div></div>
                <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Due date') }}</div><div class="fw-semibold">{{ $task->due_date?->format('d/m/Y') ?: '—' }}</div></div>
                <div class="col-6 col-md-3"><div class="small text-secondary">{{ __('Estimated') }}</div><div class="fw-semibold">{{ $task->estimated_minutes ? number_format($task->estimated_minutes/60,1).' h':'—' }}</div></div>
                <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Project') }}</div><a class="fw-semibold" href="{{ route('projects.show',$task->project) }}">{{ $task->project->code }} — {{ $task->project->name }}</a></div>
                <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Company') }}</div><a class="fw-semibold" href="{{ route('companies.show',$task->project->company) }}">{{ $task->project->company->name }}</a></div>
                <div class="col-12 col-md-4"><div class="small text-secondary">{{ __('Assignee') }}</div><div class="fw-semibold">{{ $task->assignee?->name ?: __('Unassigned') }}</div></div>
                <div class="col-12 col-md-4"><div class="small text-secondary">{{ __('Parent task') }}</div><div class="fw-semibold">@if($task->parent)<a href="{{ route('tasks.show',$task->parent) }}">{{ $task->parent->title }}</a>@else—@endif</div></div>
                <div class="col-12 col-md-4"><div class="small text-secondary">{{ __('Milestone') }}</div><div class="fw-semibold">{{ $task->milestone?->name ?: '—' }}</div></div>
                <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Recurrence') }}</div><div class="fw-semibold">{{ $task->recurrence->label() }}@if($task->recurrence->value!=='none') · {{ __('every :count', ['count'=>$task->recurrence_interval]) }}@endif</div></div>
                <div class="col-12 col-md-6"><div class="small text-secondary">{{ __('Tracked time') }}</div><div class="fw-semibold">{{ number_format($task->trackedMinutes()/60,1) }} h</div></div>
                <div class="col-12"><div class="small text-secondary mb-1">{{ __('Description') }}</div><div>{!! nl2br(e($task->description ?: '—')) !!}</div></div>
            </div></div></div>
        </div>
        <div class="col-12 col-xl-4">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Dependencies') }}</h2><p class="fm-card-subtitle">{{ __('Work that blocks or depends on this task') }}</p></div></div><div class="card-body">
                <div class="mb-4"><div class="small fw-semibold text-uppercase text-secondary mb-2">{{ __('Blocked by') }}</div>@forelse($task->dependencies as $dependency)<a class="fm-dependency-item" href="{{ route('tasks.show',$dependency) }}"><span>{{ $dependency->title }}</span><span class="badge {{ in_array($dependency->status->value,['completed','cancelled']) ? 'text-bg-success':'text-bg-warning' }}">{{ $dependency->status->label() }}</span></a>@empty<div class="small text-secondary">{{ __('No dependencies.') }}</div>@endforelse</div>
                <div><div class="small fw-semibold text-uppercase text-secondary mb-2">{{ __('Blocking') }}</div>@forelse($task->dependents as $dependent)<a class="fm-dependency-item" href="{{ route('tasks.show',$dependent) }}"><span>{{ $dependent->title }}</span><span class="badge text-bg-light">{{ $dependent->status->label() }}</span></a>@empty<div class="small text-secondary">{{ __('This task does not block other tasks.') }}</div>@endforelse</div>
            </div></div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-12 col-xl-6">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Subtasks') }}</h2><p class="fm-card-subtitle">{{ __('Break the work into smaller deliverables') }}</p></div>@can('create',App\Models\Task::class)<a class="btn btn-sm btn-outline-primary" href="{{ route('tasks.create',['parent'=>$task->id]) }}">{{ __('Add subtask') }}</a>@endcan</div><div class="list-group list-group-flush">@forelse($task->subtasks as $subtask)<a href="{{ route('tasks.show',$subtask) }}" class="list-group-item list-group-item-action py-3"><div class="d-flex justify-content-between gap-3"><div><div class="fw-semibold">{{ $subtask->title }}</div><div class="small text-secondary">{{ $subtask->assignee?->name ?: __('Unassigned') }} · {{ $subtask->due_date?->format('d/m/Y') ?: __('No due date') }}</div></div><span class="badge text-bg-light align-self-start">{{ $subtask->status->label() }}</span></div></a>@empty<div class="p-4 text-secondary">{{ __('No subtasks yet.') }}</div>@endforelse</div></div>
        </div>
        <div class="col-12 col-xl-6">
            <div class="card fm-card h-100"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Time tracking') }}</h2><p class="fm-card-subtitle">{{ __('Track actual effort against the estimate') }}</p></div><span class="badge text-bg-light">{{ number_format($task->trackedMinutes()/60,1) }} h</span></div><div class="card-body">
                @can('update',$task)<div class="d-flex flex-wrap gap-2 mb-3">@if($runningTimer)<form method="POST" action="{{ route('tasks.time.stop',$task) }}">@csrf @method('PATCH')<button class="btn btn-danger" type="submit"><i class="bi bi-stop-circle me-1"></i>{{ __('Stop timer') }}</button></form><span class="align-self-center small text-secondary">{{ __('Started :time',['time'=>$runningTimer->started_at->format('H:i')]) }}</span>@else<form method="POST" action="{{ route('tasks.time.start',$task) }}">@csrf<button class="btn btn-success" type="submit"><i class="bi bi-play-circle me-1"></i>{{ __('Start timer') }}</button></form>@endif</div>
                <details class="mb-3"><summary class="small fw-semibold text-primary">{{ __('Add time manually') }}</summary><form method="POST" action="{{ route('tasks.time.store',$task) }}" class="row g-2 mt-2">@csrf<div class="col-4"><input class="form-control form-control-sm" type="date" name="date" value="{{ today()->format('Y-m-d') }}" required></div><div class="col-3"><input class="form-control form-control-sm" type="number" min="1" max="1440" name="minutes" placeholder="{{ __('Minutes') }}" required></div><div class="col-5"><input class="form-control form-control-sm" name="note" maxlength="500" placeholder="{{ __('Note') }}"></div><div class="col-12 text-end"><button class="btn btn-sm btn-outline-primary">{{ __('Add time') }}</button></div></form></details>@endcan
                <div class="fm-time-list">@forelse($task->timeEntries as $entry)<div class="fm-time-entry"><div><div class="fw-semibold">{{ $entry->user->name }} · {{ $entry->minutes }} {{ __('min') }}</div><div class="small text-secondary">{{ $entry->started_at->format('d/m/Y H:i') }}{{ $entry->note ? ' · '.$entry->note:'' }}</div></div>@if(auth()->id()===$entry->user_id || auth()->user()->hasRole('administrator'))<form method="POST" action="{{ route('tasks.time.destroy',[$task,$entry]) }}">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger"><i class="bi bi-trash"></i></button></form>@endif</div>@empty<div class="text-secondary small">{{ __('No time has been tracked yet.') }}</div>@endforelse</div>
            </div></div>
        </div>
    </div>

    @include('partials.collaboration-panel', ['collaborationTarget' => $task])
@endsection
