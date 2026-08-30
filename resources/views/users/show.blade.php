@extends('layouts.app')
@section('title', $user->name)
@section('page-title', $user->name)
@section('page-subtitle'){{ $user->email }}@endsection
@section('content')
    <div class="d-flex justify-content-end gap-2 mb-4">@can('update', $user)<a href="{{ route('users.edit', $user) }}" class="btn btn-primary"><i class="bi bi-pencil me-1"></i> {{ __('Edit user') }}</a>@endcan</div>
    <div class="row g-4"><div class="col-12 col-xl-7"><div class="card fm-card"><div class="card-header fm-card-header"><div><h2 class="fm-card-title">{{ __('Roles and permissions') }}</h2><p class="fm-card-subtitle">{{ __('Effective access granted to this account') }}</p></div></div><div class="card-body">
        <div class="mb-4"><div class="small text-secondary mb-2">{{ __('Assigned roles') }}</div>@forelse ($user->roles as $role)<a href="{{ route('roles.show', $role) }}" class="badge text-bg-primary me-1">{{ __($role->name) }}</a>@empty<span class="text-secondary">{{ __('No roles assigned.') }}</span>@endforelse</div>
        <div><div class="small text-secondary mb-2">{{ __('Effective permissions') }}</div><div class="d-flex flex-wrap gap-2">@forelse ($user->roles->flatMap->permissions->unique('id')->sortBy('slug') as $permission)<span class="badge text-bg-light border text-secondary">{{ $permission->slug }}</span>@empty<span class="text-secondary">{{ __('No permissions.') }}</span>@endforelse</div></div>
    </div></div></div><div class="col-12 col-xl-5"><div class="card fm-card"><div class="card-body"><h2 class="fm-card-title mb-3">{{ __('Account information') }}</h2><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">{{ __('Email') }}</span><strong>{{ $user->email }}</strong></div><div class="d-flex justify-content-between py-2 border-bottom"><span class="text-secondary">{{ __('Created') }}</span><strong>{{ $user->created_at->format('d/m/Y H:i') }}</strong></div><div class="d-flex justify-content-between pt-2"><span class="text-secondary">{{ __('Updated') }}</span><strong>{{ $user->updated_at->format('d/m/Y H:i') }}</strong></div></div></div></div></div>
@endsection
