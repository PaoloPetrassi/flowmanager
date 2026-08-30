@extends('layouts.app')
@section('title', $project->name)
@section('page-title', $project->name)
@section('page-subtitle'){{ $project->code }} · {{ $project->company->name }}@endsection
@section('content')
    <div class="d-flex justify-content-end flex-wrap gap-2 mb-4">
        @can('create', App\Models\Task::class)<a href="{{ route('tasks.create', ['project' => $project->id]) }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1"></i> New task</a>@endcan
        @can('update', $project)<a href="{{ route('projects.edit', $project) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> Edit project</a>@endcan
    </div>

    <div class="row g-4">
        <div class="col-12 col-xl-8">
            <div class="card fm-card mb-4"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">Project details</h2><p class="fm-card-subtitle">Core delivery information</p></div></div><div class="card-body">
                <div class="row g-4">
                    <div class="col-6 col-md-3"><div class="small text-secondary">Status</div><div class="fw-semibold">{{ $project->status->label() }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">Priority</div><div class="fw-semibold">{{ $project->priority->label() }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">Start</div><div class="fw-semibold">{{ $project->start_date?->format('d/m/Y') ?: '—' }}</div></div>
                    <div class="col-6 col-md-3"><div class="small text-secondary">Due</div><div class="fw-semibold">{{ $project->due_date?->format('d/m/Y') ?: '—' }}</div></div>
                    <div class="col-12 col-md-6">
                        <div class="small text-secondary">Company</div>
                        @if ($project->company->trashed())
                            <div class="fw-semibold">
                                {{ $project->company->name }}
                                <span class="badge text-bg-light border text-secondary ms-1">Archived</span>
                            </div>
                        @else
                            <a class="fw-semibold" href="{{ route('companies.show', $project->company) }}">{{ $project->company->name }}</a>
                        @endif
                    </div>
                    <div class="col-12 col-md-6"><div class="small text-secondary">Reference contact</div>@if ($project->contact)<a class="fw-semibold" href="{{ route('contacts.show', $project->contact) }}">{{ $project->contact->full_name }}</a>@else<div class="fw-semibold">—</div>@endif</div>
                    <div class="col-12 col-md-6"><div class="small text-secondary">Manager</div><div class="fw-semibold">{{ $project->manager?->name ?: 'Unassigned' }}</div></div>
                    <div class="col-12 col-md-6"><div class="small text-secondary">Budget</div><div class="fw-semibold">{{ $project->budget !== null ? '€ '.number_format((float) $project->budget, 2, ',', '.') : '—' }}</div></div>
                    <div class="col-12"><div class="small text-secondary mb-1">Description</div><div>{{ $project->description ?: '—' }}</div></div>
                    @if ($project->notes)<div class="col-12"><div class="small text-secondary mb-1">Internal notes</div><div class="border rounded p-3 bg-light">{{ $project->notes }}</div></div>@endif
                </div>
            </div></div>

            <div class="card fm-card"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">Recent tasks</h2><p class="fm-card-subtitle">Tasks linked to this project</p></div><a href="{{ route('tasks.index', ['project_id' => $project->id]) }}" class="btn btn-sm btn-outline-secondary">View all</a></div>
                <div class="table-responsive"><table class="table align-middle mb-0 fm-table"><thead><tr><th>Task</th><th>Status</th><th>Priority</th><th>Assignee</th><th>Due</th></tr></thead><tbody>
                    @forelse ($project->tasks as $task)<tr><td><a class="fw-semibold text-dark" href="{{ route('tasks.show', $task) }}">{{ $task->title }}</a></td><td>{{ $task->status->label() }}</td><td>{{ $task->priority->label() }}</td><td>{{ $task->assignee?->name ?: '—' }}</td><td>{{ $task->due_date?->format('d/m/Y') ?: '—' }}</td></tr>@empty<tr><td colspan="5" class="text-center py-4 text-secondary">No tasks linked to this project.</td></tr>@endforelse
                </tbody></table></div>
            </div>
        </div>

        <div class="col-12 col-xl-4">
            <div class="card fm-card"><div class="card-body"><h2 class="fm-card-title mb-3">Record information</h2><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created by</span><strong>{{ $project->creator?->name ?: 'System' }}</strong></div><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">Created</span><strong>{{ $project->created_at->format('d/m/Y H:i') }}</strong></div><div class="d-flex justify-content-between pt-2"><span class="text-secondary">Updated</span><strong>{{ $project->updated_at->format('d/m/Y H:i') }}</strong></div></div></div>
        </div>
    </div>
@endsection
