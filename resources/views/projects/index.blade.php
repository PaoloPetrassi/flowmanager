@extends('layouts.app')
@section('title', __('Projects'))
@section('page-title', __('Projects'))
@section('page-subtitle', __('Plan work, owners, deadlines and company delivery'))
@section('content')
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
        <div class="text-secondary">{{ trans_choice('ui.counts.projects', $projects->total(), ['count' => $projects->total()]) }}</div>
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="btn btn-primary"><i class="bi bi-plus-lg me-1"></i> {{ __('New project') }}</a>
        @endcan
    </div>

    <div class="card fm-card mb-4">
        <div class="card-body">
            <form method="GET" action="{{ route('projects.index') }}">
                <div class="row g-3">
                    <div class="col-12 col-lg-4">
                        <label for="search" class="form-label fw-semibold">{{ __('Search') }}</label>
                        <input id="search" name="search" class="form-control" value="{{ $filters['search'] }}" placeholder="{{ __('Code, name, company...') }}">
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="company_id" class="form-label fw-semibold">{{ __('Company') }}</label>
                        <select id="company_id" name="company_id" class="form-select">
                            <option value="">{{ __('All companies') }}</option>
                            @foreach ($companies as $company)<option value="{{ $company->id }}" @selected((string) $filters['company_id'] === (string) $company->id)>{{ $company->name }}</option>@endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="status" class="form-label fw-semibold">{{ __('Status') }}</label>
                        <select id="status" name="status" class="form-select"><option value="">{{ __('All statuses') }}</option>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($filters['status'] === $status->value)>{{ $status->label() }}</option>@endforeach</select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="priority" class="form-label fw-semibold">{{ __('Priority') }}</label>
                        <select id="priority" name="priority" class="form-select"><option value="">{{ __('All priorities') }}</option>@foreach ($priorities as $priority)<option value="{{ $priority->value }}" @selected($filters['priority'] === $priority->value)>{{ $priority->label() }}</option>@endforeach</select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-2">
                        <label for="manager_id" class="form-label fw-semibold">{{ __('Manager') }}</label>
                        <select id="manager_id" name="manager_id" class="form-select"><option value="">{{ __('All managers') }}</option>@foreach ($managers as $manager)<option value="{{ $manager->id }}" @selected((string) $filters['manager_id'] === (string) $manager->id)>{{ $manager->name }}</option>@endforeach</select>
                    </div>
                    <div class="col-12 col-md-6 col-lg-3">
                        <label for="sort" class="form-label fw-semibold">{{ __('Sort by') }}</label>
                        <select id="sort" name="sort" class="form-select">
                            @foreach (['name' => 'Name', 'code' => 'Code', 'status' => 'Status', 'priority' => 'Priority', 'start_date' => 'Start date', 'due_date' => 'Due date', 'created_at' => 'Created'] as $value => $label)
                                <option value="{{ $value }}" @selected($filters['sort'] === $value)>{{ __($label) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3 col-lg-2">
                        <label for="direction" class="form-label fw-semibold">{{ __('Order') }}</label>
                        <select id="direction" name="direction" class="form-select"><option value="asc" @selected($filters['direction'] === 'asc')>{{ __('Ascending') }}</option><option value="desc" @selected($filters['direction'] === 'desc')>{{ __('Descending') }}</option></select>
                    </div>
                </div>
                <div class="d-flex justify-content-end gap-2 mt-3"><a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">{{ __('Reset') }}</a><button type="submit" class="btn btn-primary">{{ __('Apply filters') }}</button></div>
            </form>
        </div>
    </div>


    @include('partials.saved-filters', ['filterResource' => 'projects', 'filterRoute' => 'projects.index'])
    <div data-bulk-container>
        @include('partials.bulk-toolbar', ['bulkResource' => 'projects', 'statuses' => $statuses, 'priorities' => $priorities])

    <div class="card fm-card">
        <div class="table-responsive">
            <table class="table align-middle mb-0 fm-table" data-table-resource="projects">
                <thead><tr><th style="width:42px"><input type="checkbox" class="form-check-input" data-bulk-select-all aria-label="{{ __('Select all') }}"></th><th>{{ __('Project') }}</th><th data-column="company">{{ __('Company') }}</th><th data-column="status">{{ __('Status') }}</th><th data-column="priority">{{ __('Priority') }}</th><th data-column="manager">{{ __('Manager') }}</th><th data-column="due_date">{{ __('Due date') }}</th><th data-column="progress">{{ __('Progress') }}</th><th data-column="tasks">{{ __('Tasks') }}</th><th class="text-end">{{ __('Actions') }}</th></tr></thead>
                <tbody>
                    @forelse ($projects as $project)
                        @php
                            $statusClass = match ($project->status->value) {'active' => 'text-bg-primary', 'completed' => 'text-bg-success', 'on_hold' => 'text-bg-warning', 'cancelled' => 'text-bg-secondary', default => 'text-bg-light border text-secondary'};
                            $priorityClass = match ($project->priority->value) {'urgent' => 'text-bg-danger', 'high' => 'text-bg-warning', 'low' => 'text-bg-light border text-secondary', default => 'text-bg-primary'};
                        @endphp
                        <tr>
                            <td><input type="checkbox" class="form-check-input" value="{{ $project->id }}" data-bulk-checkbox aria-label="{{ __('Select') }}"></td>
                            <td><a class="fw-semibold text-dark" href="{{ route('projects.show', $project) }}">{{ $project->name }}</a><div class="small text-secondary">{{ $project->code }}</div></td>
                            <td data-column="company">
                                @if ($project->company->trashed())
                                    <span>{{ $project->company->name }}</span>
                                    <span class="badge text-bg-light border text-secondary ms-1">{{ __('Archived') }}</span>
                                @else
                                    <a href="{{ route('companies.show', $project->company) }}">{{ $project->company->name }}</a>
                                @endif
                            </td>
                            <td data-column="status"><span class="badge {{ $statusClass }}">{{ $project->status->label() }}</span></td>
                            <td data-column="priority"><span class="badge {{ $priorityClass }}">{{ $project->priority->label() }}</span></td>
                            <td data-column="manager">{{ $project->manager?->name ?: '—' }}</td>
                            <td data-column="due_date">{{ $project->due_date?->format('d/m/Y') ?: '—' }}</td>
                            <td data-column="progress" style="min-width:120px"><div class="d-flex align-items-center gap-2"><div class="progress flex-grow-1" style="height:6px"><div class="progress-bar" style="width: {{ $project->progressPercentage() }}%"></div></div><span class="small">{{ $project->progressPercentage() }}%</span></div></td>
                            <td data-column="tasks">{{ $project->tasks_count }}</td>
                            <td class="text-end"><div class="btn-group"><a href="{{ route('projects.show', $project) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('View') }}"><i class="bi bi-eye"></i></a>@can('update', $project)<a href="{{ route('projects.edit', $project) }}" class="btn btn-sm btn-outline-secondary" title="{{ __('Edit') }}"><i class="bi bi-pencil"></i></a>@endcan @can('delete', $project)<form method="POST" action="{{ route('projects.destroy', $project) }}" onsubmit="return confirm(@js(__('Delete this project?')));">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger rounded-start-0" title="{{ __('Delete') }}"><i class="bi bi-trash"></i></button></form>@endcan</div></td>
                        </tr>
                    @empty
                        <tr><td colspan="10" class="text-center py-5"><i class="bi bi-kanban fs-1 text-secondary"></i><div class="fw-semibold mt-3">{{ __('No projects found') }}</div><div class="text-secondary">{{ __('Create a project or change the active filters.') }}</div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($projects->hasPages())<div class="card-footer bg-white p-3">{{ $projects->links() }}</div>@endif
    </div>
    </div>
@endsection
