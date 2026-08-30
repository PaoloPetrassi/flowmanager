@extends('layouts.app')
@section('title', __('Assign asset'))
@section('page-title', __('Assign asset'))
@section('page-subtitle'){{ $asset->asset_tag }} — {{ $asset->name }}@endsection
@section('content')
    <div class="card fm-card"><form method="POST" action="{{ route('assets.assignment.update', $asset) }}">@csrf @method('PUT')<div class="card-body p-4">
        @if ($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
        <div class="mb-3"><div class="small text-secondary">{{ __('Current assignment') }}</div><div class="fw-semibold">{{ $asset->assignee?->name ?: __('Unassigned') }}</div></div>
        <label for="assigned_to" class="form-label fw-semibold">{{ __('Assign to user') }}</label><select id="assigned_to" name="assigned_to" class="form-select @error('assigned_to') is-invalid @enderror"><option value="">{{ __('Return to available pool') }}</option>@foreach ($users as $user)<option value="{{ $user->id }}" @selected((string) old('assigned_to', $asset->assigned_to) === (string) $user->id)>{{ $user->name }} — {{ $user->email }}</option>@endforeach</select>@error('assigned_to')<div class="invalid-feedback">{{ $message }}</div>@enderror
        <div class="form-text mt-2">{{ __('Assigning a user sets the asset status to Assigned. Removing the user sets it to Available.') }}</div>
    </div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('assets.show', $asset) }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a><button type="submit" class="btn btn-primary">{{ __('Update assignment') }}</button></div></form></div>
@endsection
