@extends('layouts.app')
@section('title', 'Edit role')
@section('page-title', 'Edit role')
@section('page-subtitle'){{ $role->name }} · {{ $role->slug }}@endsection
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('roles.update', $role) }}">@csrf @method('PUT')<div class="card-body p-4">@include('roles._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('roles.show', $role) }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Save changes</button></div></form></div>@endsection
