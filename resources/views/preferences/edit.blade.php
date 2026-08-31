@extends('layouts.app')
@section('title', __('Preferences'))
@section('page-title', __('Preferences'))
@section('page-subtitle', __('Personalize appearance, dashboard widgets and table columns'))
@section('content')
@php
    $selectedWidgets = old('dashboard_widgets', $preferences->dashboard_widgets ?: array_keys($dashboardOptions));
    $tablePreferences = old('table_preferences', $preferences->table_preferences ?: []);
@endphp
<form method="POST" action="{{ route('preferences.update') }}">@csrf @method('PUT')
<div class="row g-4">
    <div class="col-12 col-xl-5">
        <div class="card fm-card mb-4"><div class="card-body"><h5>{{ __('Appearance') }}</h5><div class="row g-3"><div class="col-md-6"><label class="form-label">{{ __('Theme') }}</label><select name="theme" class="form-select"><option value="light" @selected($preferences->theme==='light')>{{ __('Light') }}</option><option value="dark" @selected($preferences->theme==='dark')>{{ __('Dark') }}</option><option value="system" @selected($preferences->theme==='system')>{{ __('System') }}</option></select></div><div class="col-md-6"><label class="form-label">{{ __('Density') }}</label><select name="density" class="form-select"><option value="comfortable" @selected($preferences->density==='comfortable')>{{ __('Comfortable') }}</option><option value="compact" @selected($preferences->density==='compact')>{{ __('Compact') }}</option></select></div></div></div></div>
        <div class="card fm-card"><div class="card-body"><h5>{{ __('Dashboard widgets') }}</h5><p class="text-secondary small">{{ __('Choose which dashboard sections are visible to you.') }}</p>@foreach($dashboardOptions as $key=>$label)<div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="dashboard_widgets[]" value="{{ $key }}" id="widget-{{ $key }}" @checked(in_array($key,$selectedWidgets,true))><label class="form-check-label" for="widget-{{ $key }}">{{ $label }}</label></div>@endforeach</div></div>
    </div>
    <div class="col-12 col-xl-7"><div class="card fm-card"><div class="card-body"><h5>{{ __('Table columns') }}</h5><p class="text-secondary small">{{ __('Keep only the columns you normally need. Primary record and action columns always remain visible.') }}</p>@foreach($tableOptions as $resource=>$columns)@php($selected = $tablePreferences[$resource] ?? array_keys($columns))<div class="mb-4"><div class="fw-semibold text-capitalize mb-2">{{ __($resource) }}</div><div class="row">@foreach($columns as $key=>$label)<div class="col-sm-6"><div class="form-check mb-2"><input class="form-check-input" type="checkbox" name="table_preferences[{{ $resource }}][]" value="{{ $key }}" id="col-{{ $resource }}-{{ $key }}" @checked(in_array($key,$selected,true))><label class="form-check-label" for="col-{{ $resource }}-{{ $key }}">{{ $label }}</label></div></div>@endforeach</div></div>@endforeach</div></div></div>
</div>
<div class="d-flex justify-content-end mt-4"><button class="btn btn-primary"><i class="bi bi-check-lg me-1"></i>{{ __('Save preferences') }}</button></div>
</form>
@endsection
