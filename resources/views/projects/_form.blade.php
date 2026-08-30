@php
    $selectedStatus = old('status', $project->status?->value ?? 'planned');
    $selectedPriority = old('priority', $project->priority?->value ?? 'medium');
@endphp

@if ($errors->any())
    <div class="alert alert-danger">
        <div class="fw-semibold mb-2">{{ __('Please correct the highlighted fields.') }}</div>
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-3">
    <div class="col-12 col-md-4">
        <label for="code" class="form-label fw-semibold">{{ __('Project code') }}</label>
        <input id="code" name="code" type="text" maxlength="50" class="form-control @error('code') is-invalid @enderror" value="{{ old('code', $project->code) }}" required>
        @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-8">
        <label for="name" class="form-label fw-semibold">{{ __('Project name') }}</label>
        <input id="name" name="name" type="text" maxlength="180" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $project->name) }}" required>
        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="company_id" class="form-label fw-semibold">{{ __('Company') }}</label>
        <select id="company_id" name="company_id" class="form-select @error('company_id') is-invalid @enderror" required>
            <option value="">{{ __('Select a company') }}</option>
            @foreach ($companies as $company)
                <option value="{{ $company->id }}" @selected((string) old('company_id', $project->company_id) === (string) $company->id)>{{ $company->name }}</option>
            @endforeach
        </select>
        @error('company_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="contact_id" class="form-label fw-semibold">{{ __('Reference contact') }}</label>
        <select id="contact_id" name="contact_id" class="form-select @error('contact_id') is-invalid @enderror" data-company-contact-select data-company-source="company_id">
            <option value="">{{ __('No reference contact') }}</option>
            @foreach ($contacts as $contact)
                <option value="{{ $contact->id }}" data-company-id="{{ $contact->company_id }}" @selected((string) old('contact_id', $project->contact_id) === (string) $contact->id)>
                    {{ $contact->full_name }}{{ $contact->company ? ' — '.$contact->company->name : '' }}
                </option>
            @endforeach
        </select>
        @error('contact_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-6">
        <label for="manager_id" class="form-label fw-semibold">{{ __('Project manager') }}</label>
        <select id="manager_id" name="manager_id" class="form-select @error('manager_id') is-invalid @enderror">
            <option value="">{{ __('Unassigned') }}</option>
            @foreach ($managers as $manager)
                <option value="{{ $manager->id }}" @selected((string) old('manager_id', $project->manager_id) === (string) $manager->id)>{{ $manager->name }} — {{ $manager->email }}</option>
            @endforeach
        </select>
        @error('manager_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-3">
        <label for="status" class="form-label fw-semibold">{{ __('Status') }}</label>
        <select id="status" name="status" class="form-select @error('status') is-invalid @enderror" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}" @selected($selectedStatus === $status->value)>{{ $status->label() }}</option>
            @endforeach
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-6 col-md-3">
        <label for="priority" class="form-label fw-semibold">{{ __('Priority') }}</label>
        <select id="priority" name="priority" class="form-select @error('priority') is-invalid @enderror" required>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->value }}" @selected($selectedPriority === $priority->value)>{{ $priority->label() }}</option>
            @endforeach
        </select>
        @error('priority')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="start_date" class="form-label fw-semibold">{{ __('Start date') }}</label>
        <input id="start_date" name="start_date" type="date" class="form-control @error('start_date') is-invalid @enderror" value="{{ old('start_date', $project->start_date?->format('Y-m-d')) }}">
        @error('start_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="due_date" class="form-label fw-semibold">{{ __('Due date') }}</label>
        <input id="due_date" name="due_date" type="date" class="form-control @error('due_date') is-invalid @enderror" value="{{ old('due_date', $project->due_date?->format('Y-m-d')) }}">
        @error('due_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12 col-md-4">
        <label for="budget" class="form-label fw-semibold">{{ __('Budget') }}</label>
        <div class="input-group">
            <span class="input-group-text">€</span>
            <input id="budget" name="budget" type="number" min="0" step="0.01" class="form-control @error('budget') is-invalid @enderror" value="{{ old('budget', $project->budget) }}">
            @error('budget')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12">
        <label for="description" class="form-label fw-semibold">{{ __('Description') }}</label>
        <textarea id="description" name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $project->description) }}</textarea>
        @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>

    <div class="col-12">
        <label for="notes" class="form-label fw-semibold">{{ __('Internal notes') }}</label>
        <textarea id="notes" name="notes" rows="3" class="form-control @error('notes') is-invalid @enderror">{{ old('notes', $project->notes) }}</textarea>
        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
    </div>
</div>
