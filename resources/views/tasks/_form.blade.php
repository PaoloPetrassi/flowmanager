@php
    $selectedStatus = old('status', $task->status?->value ?? 'todo');
    $selectedPriority = old('priority', $task->priority?->value ?? 'medium');
    $selectedRecurrence = old('recurrence', $task->recurrence?->value ?? 'none');
    $selectedDependencies = array_map('strval', old('dependency_ids', $selectedDependencies ?? []));
@endphp

@if ($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-2">{{ __('Please correct the highlighted fields.') }}</div><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label fw-semibold">{{ __('Task title') }}</label>
        <input id="title" name="title" type="text" maxlength="180" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $task->title) }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="project_id" class="form-label fw-semibold">{{ __('Project') }}</label>
        <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror" required data-task-project-source>
            <option value="">{{ __('Select a project') }}</option>
            @foreach ($projects as $project)<option value="{{ $project->id }}" @selected((string) old('project_id', $task->project_id) === (string) $project->id)>{{ $project->code }} — {{ $project->name }}{{ $project->company ? ' · '.$project->company->name : '' }}</option>@endforeach
        </select>
        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="assigned_to" class="form-label fw-semibold">{{ __('Assignee') }}</label>
        <select id="assigned_to" name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror"><option value="">{{ __('Unassigned') }}</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="parent_id" class="form-label fw-semibold">{{ __('Parent task') }}</label>
        <select id="parent_id" name="parent_id" class="form-select @error('parent_id') is-invalid @enderror" data-project-filtered-select data-project-source="project_id"><option value="">{{ __('No parent task') }}</option>@foreach($taskOptions as $option)<option value="{{ $option->id }}" data-project-id="{{ $option->project_id }}" @selected((string) old('parent_id',$task->parent_id)===(string)$option->id)>{{ $option->title }}</option>@endforeach</select>
        @error('parent_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="milestone_id" class="form-label fw-semibold">{{ __('Milestone') }}</label>
        <select id="milestone_id" name="milestone_id" class="form-select @error('milestone_id') is-invalid @enderror" data-project-filtered-select data-project-source="project_id"><option value="">{{ __('No milestone') }}</option>@foreach($milestones as $milestone)<option value="{{ $milestone->id }}" data-project-id="{{ $milestone->project_id }}" @selected((string) old('milestone_id',$task->milestone_id)===(string)$milestone->id)>{{ $milestone->name }}{{ $milestone->due_date ? ' · '.$milestone->due_date->format('d/m/Y') : '' }}</option>@endforeach</select>
        @error('milestone_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="status" class="form-label fw-semibold">{{ __('Status') }}</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="priority" class="form-label fw-semibold">{{ __('Priority') }}</label>
        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>@foreach ($priorities as $priority)<option value="{{ $priority->value }}" @selected($selectedPriority === $priority->value)>{{ $priority->label() }}</option>@endforeach</select>
        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="due_date" class="form-label fw-semibold">{{ __('Due date') }}</label>
        <input id="due_date" name="due_date" type="date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-3">
        <label for="estimated_minutes" class="form-label fw-semibold">{{ __('Estimated effort') }}</label>
        <div class="input-group"><input id="estimated_minutes" name="estimated_minutes" type="number" min="0" step="15" class="form-control @error('estimated_minutes') is-invalid @enderror" value="{{ old('estimated_minutes', $task->estimated_minutes) }}"><span class="input-group-text">{{ __('min') }}</span></div>
        @error('estimated_minutes')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
    </div>

    <div class="col-12"><div class="fm-form-section"><div class="fw-semibold mb-2">{{ __('Recurrence') }}</div><div class="row g-3">
        <div class="col-12 col-md-4"><label for="recurrence" class="form-label">{{ __('Repeat') }}</label><select id="recurrence" name="recurrence" class="form-select" data-recurrence-select>@foreach($recurrences as $recurrence)<option value="{{ $recurrence->value }}" @selected($selectedRecurrence===$recurrence->value)>{{ $recurrence->label() }}</option>@endforeach</select></div>
        <div class="col-6 col-md-4"><label for="recurrence_interval" class="form-label">{{ __('Every') }}</label><input id="recurrence_interval" name="recurrence_interval" type="number" min="1" max="365" class="form-control" value="{{ old('recurrence_interval',$task->recurrence_interval ?: 1) }}"></div>
        <div class="col-6 col-md-4"><label for="recurrence_ends_at" class="form-label">{{ __('Repeat until') }}</label><input id="recurrence_ends_at" name="recurrence_ends_at" type="date" class="form-control" value="{{ old('recurrence_ends_at',$task->recurrence_ends_at?->format('Y-m-d')) }}"></div>
    </div><div class="form-text">{{ __('When a recurring task is completed, FlowManager creates the next occurrence automatically.') }}</div></div></div>

    <div class="col-12">
        <label for="dependency_ids" class="form-label fw-semibold">{{ __('Dependencies') }}</label>
        <select id="dependency_ids" name="dependency_ids[]" class="form-select @error('dependency_ids') is-invalid @enderror" multiple size="5" data-project-filtered-select data-project-source="project_id">@foreach($taskOptions as $option)<option value="{{ $option->id }}" data-project-id="{{ $option->project_id }}" @selected(in_array((string)$option->id,$selectedDependencies,true))>{{ $option->title }}</option>@endforeach</select>
        <div class="form-text">{{ __('Hold Ctrl/Cmd to select multiple dependencies. Open dependencies block task completion.') }}</div>
        @error('dependency_ids')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
