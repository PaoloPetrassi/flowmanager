@extends('layouts.app')

@section('title', __('Automations'))
@section('page-title', __('Automations'))
@section('page-subtitle', __('Rules that monitor deadlines, SLA, assignments and operational workload'))

@section('content')
    @php
        $canManage = auth()->user()->hasPermission('automations.manage');
    @endphp

    <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-4">
        <div class="small text-secondary">
            {{ __('Active rules run on schedule and respect their per-subject cooldown.') }}
        </div>

        @if ($canManage)
            <form method="POST" action="{{ route('automations.run') }}">
                @csrf
                <button class="btn btn-primary" type="submit">
                    <i class="bi bi-play-fill me-1"></i>{{ __('Run active rules now') }}
                </button>
            </form>
        @endif
    </div>

    @if ($canManage)
        <div class="card fm-card mb-4">
            <div class="card-header fm-card-header">
                <div>
                    <h2 class="fm-card-title">{{ __('New automation rule') }}</h2>
                    <p class="fm-card-subtitle">{{ __('Combine a trigger, optional priority condition and action') }}</p>
                </div>
            </div>

            <div class="card-body">
                <form method="POST" action="{{ route('automations.store') }}" class="row g-3 align-items-end" data-automation-form>
                    @csrf

                    <div class="col-12 col-lg-3">
                        <label class="form-label" for="automation-name">{{ __('Name') }}</label>
                        <input id="automation-name" class="form-control" name="name" value="{{ old('name') }}" required maxlength="150">
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="automation-trigger">{{ __('Trigger') }}</label>
                        <select id="automation-trigger" class="form-select" name="trigger" required>
                            @foreach ($triggers as $trigger)
                                <option value="{{ $trigger->value }}" @selected(old('trigger') === $trigger->value)>{{ $trigger->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="automation-action">{{ __('Action') }}</label>
                        <select id="automation-action" class="form-select" name="action" required data-automation-action>
                            @foreach ($actions as $action)
                                <option value="{{ $action->value }}" @selected(old('action') === $action->value)>{{ $action->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-6 col-lg-3">
                        <label class="form-label" for="automation-cooldown">{{ __('Cooldown') }}</label>
                        <select id="automation-cooldown" class="form-select" name="cooldown_minutes" required>
                            @foreach ($cooldowns as $minutes => $label)
                                <option value="{{ $minutes }}" @selected((int) old('cooldown_minutes', 1440) === (int) $minutes)>{{ $label }}</option>
                            @endforeach
                        </select>
                        <div class="form-text">{{ __('The same rule will not repeat on the same record during this interval.') }}</div>
                    </div>

                    <div class="col-12 col-md-4">
                        <label class="form-label" for="automation-condition-priority">{{ __('Priority condition') }}</label>
                        <select id="automation-condition-priority" class="form-select" name="condition_priority">
                            <option value="">{{ __('Any priority') }}</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}" @selected(old('condition_priority') === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4" data-automation-user-field>
                        <label class="form-label" for="automation-target-user">{{ __('Target user') }}</label>
                        <select id="automation-target-user" class="form-select" name="action_user_id">
                            <option value="">—</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected((string) old('action_user_id') === (string) $user->id)>{{ $user->name }} · {{ $user->email }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 col-md-4" data-automation-priority-field>
                        <label class="form-label" for="automation-target-priority">{{ __('Target priority') }}</label>
                        <select id="automation-target-priority" class="form-select" name="action_priority">
                            <option value="">—</option>
                            @foreach ($priorities as $priority)
                                <option value="{{ $priority->value }}" @selected(old('action_priority') === $priority->value)>{{ $priority->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-12 d-flex justify-content-between align-items-center gap-3 flex-wrap">
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="new-rule-active" name="is_active" value="1" @checked(old('is_active', true))>
                            <label class="form-check-label" for="new-rule-active">{{ __('Active') }}</label>
                        </div>

                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-plus-lg me-1"></i>{{ __('Create rule') }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card fm-card">
        <div class="card-header fm-card-header">
            <div>
                <h2 class="fm-card-title">{{ __('Automation rules') }}</h2>
                <p class="fm-card-subtitle">{{ __('Preview matches before running a rule and inspect its recent execution history') }}</p>
            </div>
            <span class="badge text-bg-light">{{ $rules->count() }}</span>
        </div>

        <div class="card-body p-0">
            @forelse ($rules as $rule)
                <article class="fm-automation-rule p-4 border-bottom">
                    <div class="d-flex justify-content-between align-items-start gap-3 flex-wrap mb-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h3 class="h6 mb-0">{{ $rule->name }}</h3>
                                <span class="badge {{ $rule->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">
                                    {{ $rule->is_active ? __('Active') : __('Paused') }}
                                </span>
                                <span class="badge text-bg-light">
                                    <i class="bi bi-hourglass-split me-1"></i>{{ $cooldowns[$rule->cooldown_minutes] ?? __(':minutes min', ['minutes' => $rule->cooldown_minutes]) }}
                                </span>
                            </div>

                            <div class="small text-secondary mt-2">
                                {{ $rule->trigger->label() }}
                                <i class="bi bi-arrow-right mx-1"></i>
                                {{ $rule->action->label() }}
                                @if (filled($rule->conditions['priority'] ?? null))
                                    <span class="ms-2">· {{ __('Priority') }}: {{ __('enums.ticket_priority.'.($rule->conditions['priority'] ?? '')) }}</span>
                                @endif
                            </div>
                        </div>

                        <div class="small text-secondary text-lg-end">
                            <div>{{ __('Last run') }}: {{ $rule->last_run_at?->diffForHumans() ?: __('Never') }}</div>
                            @if ($rule->creator)
                                <div>{{ __('Created by') }} {{ $rule->creator->name }}</div>
                            @endif
                        </div>
                    </div>

                    @if ($canManage)
                        <div class="d-flex gap-2 flex-wrap mb-3">
                            <form method="POST" action="{{ route('automations.preview', $rule) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-secondary" type="submit">
                                    <i class="bi bi-eye me-1"></i>{{ __('Preview') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('automations.run-one', $rule) }}">
                                @csrf
                                <button class="btn btn-sm btn-outline-primary" type="submit" @disabled(! $rule->is_active)>
                                    <i class="bi bi-play-fill me-1"></i>{{ __('Run rule') }}
                                </button>
                            </form>

                            <form method="POST" action="{{ route('automations.toggle', $rule) }}">
                                @csrf
                                @method('PATCH')
                                <button class="btn btn-sm {{ $rule->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}" type="submit">
                                    <i class="bi {{ $rule->is_active ? 'bi-pause-fill' : 'bi-play-circle' }} me-1"></i>
                                    {{ $rule->is_active ? __('Pause') : __('Resume') }}
                                </button>
                            </form>
                        </div>
                    @endif

                    @if (is_array($preview) && (int) ($preview['rule_id'] ?? 0) === $rule->id)
                        <div class="fm-automation-preview mb-3" role="status">
                            <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap">
                                <strong>{{ __('Preview for :rule', ['rule' => $preview['rule_name'] ?? $rule->name]) }}</strong>
                                <span class="small text-secondary">{{ __('No changes were made.') }}</span>
                            </div>
                            <div class="row g-2 mt-1">
                                <div class="col-4"><div class="fm-automation-preview-stat"><strong>{{ $preview['matched'] ?? 0 }}</strong><span>{{ __('Matched') }}</span></div></div>
                                <div class="col-4"><div class="fm-automation-preview-stat"><strong>{{ $preview['eligible'] ?? 0 }}</strong><span>{{ __('Eligible now') }}</span></div></div>
                                <div class="col-4"><div class="fm-automation-preview-stat"><strong>{{ $preview['cooldown'] ?? 0 }}</strong><span>{{ __('In cooldown') }}</span></div></div>
                            </div>
                            @if (! empty($preview['items']))
                                <div class="small text-secondary mt-2">{{ __('Examples') }}: {{ implode(' · ', $preview['items']) }}</div>
                            @endif
                        </div>
                    @endif

                    @if ($canManage)
                        <details class="mb-3">
                            <summary class="small fw-semibold text-primary">{{ __('Edit rule') }}</summary>
                            <form method="POST" action="{{ route('automations.update', $rule) }}" class="row g-2 mt-2 align-items-end" data-automation-form>
                                @csrf
                                @method('PUT')

                                <div class="col-12 col-lg-4">
                                    <label class="form-label small">{{ __('Name') }}</label>
                                    <input class="form-control form-control-sm" name="name" value="{{ $rule->name }}" required maxlength="150">
                                </div>

                                <div class="col-6 col-lg-4">
                                    <label class="form-label small">{{ __('Trigger') }}</label>
                                    <select class="form-select form-select-sm" name="trigger">
                                        @foreach ($triggers as $trigger)
                                            <option value="{{ $trigger->value }}" @selected($rule->trigger === $trigger)>{{ $trigger->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-lg-4">
                                    <label class="form-label small">{{ __('Action') }}</label>
                                    <select class="form-select form-select-sm" name="action" data-automation-action>
                                        @foreach ($actions as $action)
                                            <option value="{{ $action->value }}" @selected($rule->action === $action)>{{ $action->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-lg-3">
                                    <label class="form-label small">{{ __('Priority condition') }}</label>
                                    <select class="form-select form-select-sm" name="condition_priority">
                                        <option value="">{{ __('Any priority') }}</option>
                                        @foreach ($priorities as $priority)
                                            <option value="{{ $priority->value }}" @selected(($rule->conditions['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-lg-3" data-automation-user-field>
                                    <label class="form-label small">{{ __('Target user') }}</label>
                                    <select class="form-select form-select-sm" name="action_user_id">
                                        <option value="">—</option>
                                        @foreach ($users as $user)
                                            <option value="{{ $user->id }}" @selected((string) ($rule->action_config['user_id'] ?? '') === (string) $user->id)>{{ $user->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-lg-3" data-automation-priority-field>
                                    <label class="form-label small">{{ __('Target priority') }}</label>
                                    <select class="form-select form-select-sm" name="action_priority">
                                        <option value="">—</option>
                                        @foreach ($priorities as $priority)
                                            <option value="{{ $priority->value }}" @selected(($rule->action_config['priority'] ?? '') === $priority->value)>{{ $priority->label() }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-6 col-lg-3">
                                    <label class="form-label small">{{ __('Cooldown') }}</label>
                                    <select class="form-select form-select-sm" name="cooldown_minutes">
                                        @foreach ($cooldowns as $minutes => $label)
                                            <option value="{{ $minutes }}" @selected((int) $rule->cooldown_minutes === (int) $minutes)>{{ $label }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-12 d-flex justify-content-between align-items-center gap-3">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input" id="rule-active-{{ $rule->id }}" type="checkbox" name="is_active" value="1" @checked($rule->is_active)>
                                        <label class="form-check-label small" for="rule-active-{{ $rule->id }}">{{ __('Active') }}</label>
                                    </div>
                                    <button class="btn btn-sm btn-primary" type="submit">{{ __('Save') }}</button>
                                </div>
                            </form>
                        </details>
                    @endif

                    <div class="fm-automation-runs">
                        <div class="small fw-semibold mb-1">{{ __('Recent executions') }}</div>
                        @forelse ($rule->runs as $run)
                            <div class="small d-flex justify-content-between gap-3 py-1">
                                <span class="min-w-0">
                                    <span class="badge {{ $run->status === 'success' ? 'text-bg-light text-success' : 'text-bg-light text-danger' }}">{{ __($run->status) }}</span>
                                    {{ $run->context['label'] ?? class_basename($run->subject_type ?? '') }} · {{ $run->message }}
                                </span>
                                <span class="text-secondary flex-shrink-0">{{ $run->ran_at->format('d/m H:i') }}</span>
                            </div>
                        @empty
                            <div class="small text-secondary">{{ __('No executions yet.') }}</div>
                        @endforelse
                    </div>

                    @if ($canManage)
                        <form method="POST" action="{{ route('automations.destroy', $rule) }}" class="mt-3 text-end">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-sm btn-link text-danger text-decoration-none" type="submit" data-confirm="{{ __('Delete this automation rule?') }}">
                                {{ __('Delete rule') }}
                            </button>
                        </form>
                    @endif
                </article>
            @empty
                <div class="p-5 text-center text-secondary">
                    <i class="bi bi-lightning-charge d-block fs-2 mb-2"></i>
                    {{ __('No automation rules have been created yet.') }}
                </div>
            @endforelse
        </div>
    </div>
@endsection
