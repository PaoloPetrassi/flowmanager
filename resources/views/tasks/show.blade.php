@extends('layouts.app')

@section('title', $task->title)
@section('page-title', $task->title)
@section('page-subtitle'){{ $task->project->code }} — {{ $task->project->name }}@endsection

@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <a href="{{ route('tasks.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i>
            Tasks
        </a>

        <div class="d-flex flex-wrap gap-2">
            @can('update', $task)
                @if ($task->status->value === 'completed')
                    <form method="POST" action="{{ route('tasks.reopen', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-primary">
                            <i class="bi bi-arrow-counterclockwise me-1"></i>
                            Reopen
                        </button>
                    </form>
                @elseif ($task->status->value !== 'cancelled')
                    <form method="POST" action="{{ route('tasks.complete', $task) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="btn btn-outline-success">
                            <i class="bi bi-check-lg me-1"></i>
                            Mark completed
                        </button>
                    </form>
                @endif

                <a href="{{ route('tasks.edit', $task) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i>
                    Edit task
                </a>
            @endcan
        </div>
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card">
                <div class="card-header fm-card-header">
                    <div>
                        <h2 class="fm-card-title">Task details</h2>
                        <p class="fm-card-subtitle">Assignment and delivery information</p>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-6 col-md-3"><div class="small text-secondary">Status</div><div class="fw-semibold">{{ $task->status->label() }}</div></div>
                        <div class="col-6 col-md-3"><div class="small text-secondary">Priority</div><div class="fw-semibold">{{ $task->priority->label() }}</div></div>
                        <div class="col-6 col-md-3"><div class="small text-secondary">Due date</div><div class="fw-semibold">{{ $task->due_date?->format('d/m/Y') ?: '—' }}</div></div>
                        <div class="col-6 col-md-3"><div class="small text-secondary">Completed</div><div class="fw-semibold">{{ $task->completed_at?->format('d/m/Y H:i') ?: '—' }}</div></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Project</div><a class="fw-semibold" href="{{ route('projects.show', $task->project) }}">{{ $task->project->code }} — {{ $task->project->name }}</a></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Company</div><a class="fw-semibold" href="{{ route('companies.show', $task->project->company) }}">{{ $task->project->company->name }}</a></div>
                        <div class="col-12 col-md-6"><div class="small text-secondary">Assignee</div><div class="fw-semibold">{{ $task->assignee?->name ?: 'Unassigned' }}</div></div>
                        <div class="col-12"><div class="small text-secondary mb-1">Description</div><div>{{ $task->description ?: '—' }}</div></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="fm-card-title mb-3">Record information</h2>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created by</span><strong>{{ $task->creator?->name ?: 'System' }}</strong></div>
                    <div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created</span><strong>{{ $task->created_at->format('d/m/Y H:i') }}</strong></div>
                    <div class="d-flex justify-content-between pt-2"><span class="text-secondary">Updated</span><strong>{{ $task->updated_at->format('d/m/Y H:i') }}</strong></div>
                </div>
            </div>
        </div>
    </div>
@endsection
