@extends('layouts.app')

@section('title', __('Preferences'))
@section('page-title', __('Preferences'))
@section('page-subtitle', __('Personalize appearance, dashboard, tables and notifications'))

@section('content')
@php
    $selectedWidgets = old('dashboard_widgets', $preferences->dashboard_widgets ?: array_keys($dashboardOptions));
    $tablePreferences = old('table_preferences', $preferences->table_preferences ?: []);
    $notificationPreferences = old('notification_preferences', $preferences->notification_preferences ?: []);
@endphp

<form method="POST" action="{{ route('preferences.update') }}">
    @csrf
    @method('PUT')

    <div class="row g-4">
        <div class="col-12 col-xl-5">
            <div class="card fm-card mb-4">
                <div class="card-body">
                    <h2 class="h5">{{ __('Appearance') }}</h2>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label" for="theme">{{ __('Theme') }}</label>
                            <select id="theme" name="theme" class="form-select">
                                @foreach (['light' => 'Light', 'dark' => 'Dark', 'system' => 'System'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('theme', $preferences->theme) === $value)>{{ __($label) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="density">{{ __('Density') }}</label>
                            <select id="density" name="density" class="form-select">
                                <option value="comfortable" @selected(old('density', $preferences->density) === 'comfortable')>{{ __('Comfortable') }}</option>
                                <option value="compact" @selected(old('density', $preferences->density) === 'compact')>{{ __('Compact') }}</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card fm-card mb-4">
                <div class="card-body">
                    <h2 class="h5">{{ __('Dashboard widgets') }}</h2>
                    <p class="text-secondary small">{{ __('Choose which dashboard sections are visible to you.') }}</p>
                    @foreach ($dashboardOptions as $key => $label)
                        <div class="form-check mb-2">
                            <input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="{{ $key }}" id="widget-{{ $key }}" @checked(in_array($key, $selectedWidgets, true))>
                            <label class="form-check-label" for="widget-{{ $key }}">{{ $label }}</label>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="card fm-card">
                <div class="card-body">
                    <div class="d-flex align-items-start justify-content-between gap-3 mb-3">
                        <div>
                            <h2 class="h5 mb-1">{{ __('Notification preferences') }}</h2>
                            <p class="text-secondary small mb-0">{{ __('Choose how each type of event reaches you.') }}</p>
                        </div>
                        @if (! $mailNotificationsEnabled)
                            <span class="badge text-bg-light border">{{ __('Email globally disabled') }}</span>
                        @endif
                    </div>

                    <div class="table-responsive">
                        <table class="table table-sm align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>{{ __('Event') }}</th>
                                    <th class="text-center">{{ __('In app') }}</th>
                                    <th class="text-center">{{ __('Email') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($notificationOptions as $key => $label)
                                    @php
                                        $inAppEnabled = data_get($notificationPreferences, 'in_app.'.$key, true);
                                        $mailEnabled = data_get($notificationPreferences, 'mail.'.$key, true);
                                    @endphp
                                    <tr>
                                        <td>{{ $label }}</td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="notification_preferences[in_app][{{ $key }}]" value="1" aria-label="{{ __('In-app notifications for :event', ['event' => $label]) }}" @checked($inAppEnabled)>
                                        </td>
                                        <td class="text-center">
                                            <input class="form-check-input" type="checkbox" name="notification_preferences[mail][{{ $key }}]" value="1" aria-label="{{ __('Email notifications for :event', ['event' => $label]) }}" @checked($mailEnabled)>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if (! $mailNotificationsEnabled)
                        <div class="form-text mt-3">{{ __('Email preferences are saved now and will be used if FLOWMANAGER_MAIL_NOTIFICATIONS is enabled later.') }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="card fm-card">
                <div class="card-body">
                    <h2 class="h5">{{ __('Table columns') }}</h2>
                    <p class="text-secondary small">{{ __('Keep only the columns you normally need. Primary record and action columns always remain visible.') }}</p>

                    @foreach ($tableOptions as $resource => $columns)
                        @php($selected = $tablePreferences[$resource] ?? array_keys($columns))
                        <div class="mb-4">
                            <div class="fw-semibold text-capitalize mb-2">{{ __($resource) }}</div>
                            <div class="row">
                                @foreach ($columns as $key => $label)
                                    <div class="col-sm-6">
                                        <div class="form-check mb-2">
                                            <input class="form-check-input" type="checkbox" name="table_preferences[{{ $resource }}][]" value="{{ $key }}" id="col-{{ $resource }}-{{ $key }}" @checked(in_array($key, $selected, true))>
                                            <label class="form-check-label" for="col-{{ $resource }}-{{ $key }}">{{ $label }}</label>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-end mt-4">
        <button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ __('Save preferences') }}</button>
    </div>
</form>
@endsection
