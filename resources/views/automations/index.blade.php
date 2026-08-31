@extends('layouts.app')

@section('title', __('Automations'))
@section('page-title', __('Automations'))
@section('page-subtitle', __('Rules that monitor deadlines, SLA and operational workload'))

@section('content')
    <div class="d-flex justify-content-end mb-4">
        @if (auth()->user()->hasPermission('automations.manage'))
            <form method="POST" action="{{ route('automations.run') }}">@csrf<button class="btn btn-primary" type="submit"><i class="bi bi-play-fill me-1"></i>{{ __('Run automations now') }}</button></form>
        @endif
    </div>

    @if (auth()->user()->hasPermission('automations.manage'))
        <div class="card fm-card mb-4">
            <div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('New automation rule') }}</h2><p class="fm-card-subtitle">{{ __('Create a scheduled condition and action') }}</p></div></div>
            <div class="card-body">
                <form method="POST" action="{{ route('automations.store') }}" class="row g-3 align-items-end">
                    @csrf
                    <div class="col-12 col-lg-3"><label class="form-label">{{ __('Name') }}</label><input class="form-control" name="name" required maxlength="150"></div>
                    <div class="col-12 col-md-6 col-lg-2"><label class="form-label">{{ __('Trigger') }}</label><select class="form-select" name="trigger" required>@foreach ($triggers as $trigger)<option value="{{ $trigger->value }}">{{ $trigger->label() }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-6 col-lg-2"><label class="form-label">{{ __('Action') }}</label><select class="form-select" name="action" required>@foreach ($actions as $action)<option value="{{ $action->value }}">{{ $action->label() }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-4 col-lg-2"><label class="form-label">{{ __('Priority condition') }}</label><select class="form-select" name="condition_priority"><option value="">{{ __('Any') }}</option>@foreach ($priorities as $priority)<option value="{{ $priority->value }}">{{ $priority->label() }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-4 col-lg-2"><label class="form-label">{{ __('Target user') }}</label><select class="form-select" name="action_user_id"><option value="">—</option>@foreach ($users as $user)<option value="{{ $user->id }}">{{ $user->name }}</option>@endforeach</select></div>
                    <div class="col-12 col-md-4 col-lg-1"><label class="form-label">{{ __('Set priority') }}</label><select class="form-select" name="action_priority"><option value="">—</option>@foreach ($priorities as $priority)<option value="{{ $priority->value }}">{{ $priority->label() }}</option>@endforeach</select></div>
                    <div class="col-12 d-flex justify-content-between align-items-center gap-3"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="new_rule_active" name="is_active" value="1" checked><label class="form-check-label" for="new_rule_active">{{ __('Active') }}</label></div><button class="btn btn-primary" type="submit"><i class="bi bi-plus-lg me-1"></i>{{ __('Create rule') }}</button></div>
                </form>
            </div>
        </div>
    @endif

    <div class="card fm-card">
        <div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Automation rules') }}</h2><p class="fm-card-subtitle">{{ __('Each rule runs at most once per subject per day') }}</p></div><span class="badge text-bg-light">{{ $rules->count() }}</span></div>
        <div class="card-body p-0">
            @forelse ($rules as $rule)
                <div class="fm-automation-rule p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div><div class="d-flex align-items-center gap-2"><h3 class="h6 mb-0">{{ $rule->name }}</h3><span class="badge {{ $rule->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $rule->is_active ? __('Active') : __('Paused') }}</span></div><div class="small text-secondary mt-1">{{ $rule->trigger->label() }} <i class="bi bi-arrow-right mx-1"></i> {{ $rule->action->label() }}</div></div>
                        <div class="small text-secondary">{{ __('Last run') }}: {{ $rule->last_run_at?->diffForHumans() ?: __('Never') }}</div>
                    </div>

                    @if (auth()->user()->hasPermission('automations.manage'))
                        <details class="mb-3"><summary class="small fw-semibold text-primary">{{ __('Edit rule') }}</summary>
                            <form method="POST" action="{{ route('automations.update', $rule) }}" class="row g-2 mt-2 align-items-end">@csrf @method('PUT')
                                <div class="col-12 col-lg-3"><label class="form-label small">{{ __('Name') }}</label><input class="form-control form-control-sm" name="name" value="{{ $rule->name }}" required></div>
                                <div class="col-6 col-lg-2"><label class="form-label small">{{ __('Trigger') }}</label><select class="form-select form-select-sm" name="trigger">@foreach ($triggers as $trigger)<option value="{{ $trigger->value }}" @selected($rule->trigger === $trigger)>{{ $trigger->label() }}</option>@endforeach</select></div>
                                <div class="col-6 col-lg-2"><label class="form-label small">{{ __('Action') }}</label><select class="form-select form-select-sm" name="action">@foreach ($actions as $action)<option value="{{ $action->value }}" @selected($rule->action === $action)>{{ $action->label() }}</option>@endforeach</select></div>
                                <div class="col-6 col-lg-2"><label class="form-label small">{{ __('Priority condition') }}</label><select class="form-select form-select-sm" name="condition_priority"><option value="">{{ __('Any') }}</option>@foreach ($priorities as $priority)<option value="{{ $priority->value }}" @selected(($rule->conditions['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>@endforeach</select></div>
                                <div class="col-6 col-lg-2"><label class="form-label small">{{ __('Target user') }}</label><select class="form-select form-select-sm" name="action_user_id"><option value="">—</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) ($rule->action_config['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>@endforeach</select></div>
                                <div class="col-6 col-lg-1"><label class="form-label small">{{ __('Priority') }}</label><select class="form-select form-select-sm" name="action_priority"><option value="">—</option>@foreach ($priorities as $priority)<option value="{{ $priority->value }}" @selected(($rule->action_config['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>@endforeach</select></div>
                                <div class="col-12 d-flex justify-content-between align-items-center"><div class="form-check form-switch"><input class="form-check-input" type="checkbox" name="is_active" value="1" @checked($rule->is_active)><label class="form-check-label small">{{ __('Active') }}</label></div><button class="btn btn-sm btn-primary">{{ __('Save') }}</button></div>
                            </form>
                        </details>
                    @endif

                    <div class="fm-automation-runs">
                        @forelse ($rule->runs as $run)
                            <div class="small d-flex justify-content-between gap-3 py-1"><span><span class="badge {{ $run->status === 'success' ? 'text-bg-light text-success' : 'text-bg-light text-danger' }}">{{ __($run->status) }}</span> {{ $run->context['label'] ?? class_basename($run->subject_type ?? '') }} · {{ $run->message }}</span><span class="text-secondary flex-shrink-0">{{ $run->ran_at->format('d/m H:i') }}</span></div>
                        @empty
                            <div class="small text-secondary">{{ __('No executions yet.') }}</div>
                        @endforelse
                    </div>

                    @if (auth()->user()->hasPermission('automations.manage'))
                        <form method="POST" action="{{ route('automations.destroy', $rule) }}" class="mt-3 text-end">@csrf @method('DELETE')<button class="btn btn-sm btn-link text-danger text-decoration-none" data-confirm="{{ __('Delete this automation rule?') }}">{{ __('Delete rule') }}</button></form>
                    @endif
                </div>
            @empty
                <div class="p-5 text-center text-secondary">{{ __('No automation rules have been created yet.') }}</div>
            @endforelse
        </div>
    </div>
@endsection
