@extends('layouts.app')
@section('title', 'Edit user')
@section('page-title', 'Edit user')
@section('page-subtitle'){{ $user->name }} · {{ $user->email }}@endsection
@section('content')<div class="card fm-card"><form method="POST" action="{{ route('users.update', $user) }}">@csrf @method('PUT')<div class="card-body p-4">@include('users._form')</div><div class="card-footer bg-white d-flex justify-content-end gap-2 p-3"><a href="{{ route('users.show', $user) }}" class="btn btn-outline-secondary">Cancel</a><button class="btn btn-primary" type="submit">Save changes</button></div></form></div>@endsection
