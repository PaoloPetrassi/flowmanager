@extends('layouts.app')

@section('title', __('Project templates'))
@section('page-title', __('Project templates'))
@section('page-subtitle', __('Reusable project structures with tasks and subtasks'))

@section('content')
    <div class="row g-4">
        @forelse ($templates as $template)
            <div class="col-12 col-xl-6">
                <div class="card fm-card h-100">
                    <div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ $template->name }}</h2><p class="fm-card-subtitle">{{ $template->code }} · {{ trans_choice('ui.counts.tasks', $template->tasks_count, ['count' => $template->tasks_count]) }}</p></div><span class="badge text-bg-light">{{ __('Template') }}</span></div>
                    <div class="card-body">
                        <p class="text-secondary">{{ $template->description ?: __('No description.') }}</p>
                        @can('create', App\Models\Project::class)
                            <form method="POST" action="{{ route('project-templates.instantiate', $template) }}" class="row g-3">@csrf
                                <div class="col-12"><label class="form-label">{{ __('New project name') }}</label><input class="form-control" name="name" value="{{ preg_replace('/\s+Template$/', '', $template->name) }}" required></div>
                                <div class="col-12 col-md-6"><label class="form-label">{{ __('Company') }}</label><select class="form-select" name="company_id" required><option value="">{{ __('Select a company') }}</option>@foreach ($companies as $company)<option value="{{ $company->id }}">{{ $company->name }}</option>@endforeach</select></div>
                                <div class="col-12 col-md-6"><label class="form-label">{{ __('Project manager') }}</label><select class="form-select" name="manager_id"><option value="">{{ __('Unassigned') }}</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                                <div class="col-6"><label class="form-label">{{ __('Start date') }}</label><input class="form-control" type="date" name="start_date"></div>
                                <div class="col-6"><label class="form-label">{{ __('Due date') }}</label><input class="form-control" type="date" name="due_date"></div>
                                <div class="col-12 d-flex justify-content-between gap-2"><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>{{ __('Create project') }}</button>
                                    @can('delete', $template)<button class="btn btn-outline-danger" type="submit" form="delete-template-{{ $template->id }}" data-confirm="{{ __('Delete this template?') }}">{{ __('Delete') }}</button>@endcan
                                </div>
                            </form>
                            @can('delete', $template)<form id="delete-template-{{ $template->id }}" method="POST" action="{{ route('project-templates.destroy', $template) }}">@csrf @method('DELETE')</form>@endcan
                        @endcan
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12"><div class="card fm-card"><div class="card-body p-5 text-center text-secondary"><i class="bi bi-copy display-5 d-block mb-3"></i>{{ __('No project templates yet. Open a project and choose “Save as template”.') }}</div></div></div>
        @endforelse
    </div>
@endsection
