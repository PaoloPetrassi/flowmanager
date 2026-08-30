@php
    $selectedStatus = old('status', $task->status?->value ?? 'todo');
    $selectedPriority = old('priority', $task->priority?->value ?? 'medium');
@endphp

@if ($errors->any())
    <div class="alert alert-danger"><div class="fw-semibold mb-2">Please correct the highlighted fields.</div><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
@endif

<div class="row g-3">
    <div class="col-12">
        <label for="title" class="form-label fw-semibold">Task title</label>
        <input id="title" name="title" type="text" maxlength="180" class="form-control @error('title') is-invalid @enderror" value="{{ old('title', $task->title) }}" required>
        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="project_id" class="form-label fw-semibold">Project</label>
        <select id="project_id" name="project_id" class="form-select @error('project_id') is-invalid @enderror" required>
            <option value="">Select a project</option>
            @foreach ($projects as $project)<option value="{{ $project->id }}" @selected((string) old('project_id', $task->project_id) === (string) $project->id)>{{ $project->code }} — {{ $project->name }}{{ $project->company ? ' · '.$project->company->name : '' }}</option>@endforeach
        </select>
        @error('project_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-6">
        <label for="assigned_to" class="form-label fw-semibold">Assignee</label>
        <select id="assigned_to" name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror"><option value="">Unassigned</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) old('assigned_to', $task->assigned_to) === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select>
        @error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-4">
        <label for="status" class="form-label fw-semibold">Status</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>@foreach ($statuses as $status)<option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>@endforeach</select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-6 col-md-4">
        <label for="priority" class="form-label fw-semibold">Priority</label>
        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>@foreach ($priorities as $priority)<option value="{{ $priority->value }}" @selected($selectedPriority === $priority->value)>{{ $priority->label() }}</option>@endforeach</select>
        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12 col-md-4">
        <label for="due_date" class="form-label fw-semibold">Due date</label>
        <input id="due_date" name="due_date" type="date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $task->due_date?->format('Y-m-d')) }}">
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
    <div class="col-12">
        <label for="description" class="form-label fw-semibold">Description</label>
        <textarea id="description" name="description" rows="5" class="form-control @error('description') is-invalid @enderror">{{ old('description', $task->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
